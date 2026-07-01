<?php

namespace App\Services\Reports;

use Illuminate\Contracts\Pagination\Paginator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentExportService
{
    public function csv(string $filename, array $rows): StreamedResponse
    {
        return response()->streamDownload(function () use ($rows): void {
            $handle = fopen('php://output', 'w');
            $flatRows = $this->flatRows($rows);

            if ($flatRows === []) {
                fputcsv($handle, ['empty']);
                fclose($handle);

                return;
            }

            fputcsv($handle, array_keys($flatRows[0]));
            foreach ($flatRows as $row) {
                fputcsv($handle, $row);
            }
            fclose($handle);
        }, $this->ensureExtension($filename, 'csv'), [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function pdf(string $filename, string $title, array $lines): Response
    {
        $content = $this->buildPdf($title, $lines);

        return response($content, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$this->ensureExtension($filename, 'pdf').'"',
        ]);
    }

    public function rowsFromPayload(mixed $payload): array
    {
        if ($payload instanceof Paginator) {
            return $payload->items();
        }

        if (is_array($payload) && array_key_exists('data', $payload)) {
            return is_array($payload['data']) ? $payload['data'] : $this->rowsFromPayload($payload['data']);
        }

        if (is_iterable($payload)) {
            return is_array($payload) ? $payload : iterator_to_array($payload);
        }

        return [$payload];
    }

    public function linesFromPayload(string $title, mixed $payload): array
    {
        $rows = $this->flatRows($this->rowsFromPayload($payload));
        $lines = [$title, 'Generado: '.now()->toDateTimeString(), ''];

        if ($rows === []) {
            return array_merge($lines, ['Sin datos para los filtros solicitados.']);
        }

        foreach (array_slice($rows, 0, 80) as $index => $row) {
            $lines[] = 'Registro '.($index + 1);
            foreach ($row as $key => $value) {
                $lines[] = $key.': '.$value;
            }
            $lines[] = '';
        }

        if (count($rows) > 80) {
            $lines[] = 'El PDF muestra los primeros 80 registros. Use CSV para el detalle completo.';
        }

        return $lines;
    }

    private function flatRows(array $rows): array
    {
        return collect($rows)
            ->map(fn ($row) => $this->flatten((array) $this->normalize($row)))
            ->map(fn (array $row) => collect($row)->map(fn ($value) => is_scalar($value) || $value === null ? $value : json_encode($value))->all())
            ->values()
            ->all();
    }

    private function normalize(mixed $value): mixed
    {
        if ($value instanceof \JsonSerializable) {
            return $value->jsonSerialize();
        }

        if ($value instanceof \Illuminate\Contracts\Support\Arrayable) {
            return $value->toArray();
        }

        if (is_object($value)) {
            return get_object_vars($value);
        }

        return $value;
    }

    private function flatten(array $row, string $prefix = ''): array
    {
        $flat = [];
        foreach ($row as $key => $value) {
            $name = $prefix === '' ? (string) $key : $prefix.'.'.$key;
            $value = $this->normalize($value);

            if (is_array($value)) {
                $flat += $this->flatten($value, $name);
                continue;
            }

            $flat[$name] = $value;
        }

        return $flat;
    }

    private function buildPdf(string $title, array $lines): string
    {
        $pages = array_chunk($this->wrapLines($lines), 48);
        $objects = [
            1 => '<< /Type /Catalog /Pages 2 0 R >>',
            2 => '',
            3 => '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>',
        ];
        $pageObjectNumbers = [];

        foreach ($pages as $index => $pageLines) {
            $contentObject = 4 + ($index * 2);
            $pageObject = $contentObject + 1;
            $pageObjectNumbers[] = $pageObject;

            $content = "BT\n/F1 10 Tf\n50 790 Td\n14 TL\n";
            foreach ($pageLines as $line) {
                $content .= '('.$this->escapePdfText($line).") Tj\nT*\n";
            }
            $content .= "ET\n";

            $objects[$contentObject] = "<< /Length ".strlen($content)." >>\nstream\n".$content."endstream";
            $objects[$pageObject] = "<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Resources << /Font << /F1 3 0 R >> >> /Contents {$contentObject} 0 R >>";
        }

        $objects[2] = '<< /Type /Pages /Kids ['.implode(' ', array_map(fn ($number) => $number.' 0 R', $pageObjectNumbers)).'] /Count '.count($pageObjectNumbers).' >>';
        ksort($objects);

        $pdf = "%PDF-1.4\n% ".$this->escapePdfText($title)."\n";
        $offsets = [0];
        foreach ($objects as $number => $object) {
            $offsets[$number] = strlen($pdf);
            $pdf .= $number." 0 obj\n".$object."\nendobj\n";
        }

        $xrefOffset = strlen($pdf);
        $pdf .= "xref\n0 ".(count($objects) + 1)."\n0000000000 65535 f \n";
        for ($number = 1; $number <= count($objects); $number++) {
            $pdf .= str_pad((string) $offsets[$number], 10, '0', STR_PAD_LEFT)." 00000 n \n";
        }

        return $pdf."trailer\n<< /Size ".(count($objects) + 1)." /Root 1 0 R >>\nstartxref\n{$xrefOffset}\n%%EOF";
    }

    private function wrapLines(array $lines): array
    {
        return collect($lines)
            ->flatMap(fn ($line) => str_split((string) $line, 92) ?: [''])
            ->all();
    }

    private function escapePdfText(string $text): string
    {
        return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $this->ascii($text));
    }

    private function ascii(string $text): string
    {
        $converted = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);

        return $converted === false ? $text : $converted;
    }

    private function ensureExtension(string $filename, string $extension): string
    {
        return str_ends_with($filename, '.'.$extension) ? $filename : $filename.'.'.$extension;
    }
}
