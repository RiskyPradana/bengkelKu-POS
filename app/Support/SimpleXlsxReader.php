<?php

namespace App\Support;

use SimpleXMLElement;
use ZipArchive;

/**
 * Sesi 14: pembaca file .xlsx murni PHP (ZipArchive + SimpleXML), tanpa
 * perlu tambahan package composer (phpoffice/phpspreadsheet dll). Dipakai
 * untuk import Supplier & Item dari export iPos 5.
 */
class SimpleXlsxReader
{
    /**
     * Membaca semua sheet dari sebuah file .xlsx.
     *
     * @return array<string, array<int, array<int, mixed>>> nama sheet => daftar baris (0-indexed), setiap baris array kolom (0-indexed).
     */
    public static function read(string $path): array
    {
        $zip = new ZipArchive();
        if ($zip->open($path) !== true) {
            throw new \RuntimeException("Tidak bisa membuka file xlsx: {$path}");
        }

        $sharedStrings = self::readSharedStrings($zip);
        $sheetMap      = self::readSheetMap($zip);

        $result = [];

        foreach ($sheetMap as $name => $sheetPath) {
            $xml = $zip->getFromName($sheetPath);
            if ($xml === false) {
                continue;
            }

            $result[$name] = self::parseSheet($xml, $sharedStrings);
        }

        $zip->close();

        return $result;
    }

    private static function readSharedStrings(ZipArchive $zip): array
    {
        $sharedStrings = [];
        $sharedXml = $zip->getFromName('xl/sharedStrings.xml');
        if ($sharedXml === false) {
            return $sharedStrings;
        }

        $sst = new SimpleXMLElement($sharedXml);
        foreach ($sst->si as $si) {
            if (isset($si->t)) {
                $sharedStrings[] = (string) $si->t;
            } else {
                $text = '';
                if (isset($si->r)) {
                    foreach ($si->r as $r) {
                        $text .= (string) $r->t;
                    }
                }
                $sharedStrings[] = $text;
            }
        }

        return $sharedStrings;
    }

    private static function readSheetMap(ZipArchive $zip): array
    {
        $sheetMap = [];

        $workbookXml = $zip->getFromName('xl/workbook.xml');
        $relsXml     = $zip->getFromName('xl/_rels/workbook.xml.rels');

        if ($workbookXml === false || $relsXml === false) {
            return $sheetMap;
        }

        $workbook = new SimpleXMLElement($workbookXml);
        $rels     = new SimpleXMLElement($relsXml);

        $relById = [];
        foreach ($rels->Relationship as $rel) {
            $relById[(string) $rel['Id']] = (string) $rel['Target'];
        }

        $rNamespace = 'http://schemas.openxmlformats.org/officeDocument/2006/relationships';

        foreach ($workbook->sheets->sheet as $sheet) {
            $rAttrs = $sheet->attributes($rNamespace);
            $rId    = $rAttrs !== null ? (string) $rAttrs['id'] : '';
            $target = $relById[$rId] ?? null;

            if ($target === null) {
                continue;
            }

            $target = ltrim($target, '/');
            if (! str_starts_with($target, 'xl/')) {
                $target = 'xl/' . $target;
            }

            $sheetMap[(string) $sheet['name']] = $target;
        }

        return $sheetMap;
    }

    private static function parseSheet(string $xml, array $sharedStrings): array
    {
        $sheetXml = new SimpleXMLElement($xml);
        $rows = [];

        if (! isset($sheetXml->sheetData->row)) {
            return [];
        }

        foreach ($sheetXml->sheetData->row as $row) {
            $rowIndex = (int) $row['r'];
            $cells = [];

            foreach ($row->c as $c) {
                $ref = (string) $c['r'];
                $colLetters = preg_replace('/[0-9]/', '', $ref) ?? '';
                $colIndex = self::colLettersToIndex($colLetters);

                $type = (string) $c['t'];
                $value = null;

                if (isset($c->is)) {
                    $value = (string) $c->is->t;
                } elseif (isset($c->v)) {
                    $raw = (string) $c->v;
                    if ($type === 's') {
                        $value = $sharedStrings[(int) $raw] ?? '';
                    } elseif ($type === 'b') {
                        $value = ((int) $raw) === 1;
                    } elseif ($type === 'str' || $type === 'e') {
                        $value = $raw;
                    } else {
                        $value = is_numeric($raw)
                            ? (str_contains($raw, '.') ? (float) $raw : (int) $raw)
                            : $raw;
                    }
                }

                $cells[$colIndex] = $value;
            }

            if (! empty($cells)) {
                $maxCol = max(array_keys($cells));
                $line = [];
                for ($i = 0; $i <= $maxCol; $i++) {
                    $line[$i] = $cells[$i] ?? null;
                }
                $rows[$rowIndex] = $line;
            }
        }

        ksort($rows);

        return array_values($rows);
    }

    private static function colLettersToIndex(string $letters): int
    {
        $letters = strtoupper($letters);
        $index = 0;
        for ($i = 0; $i < strlen($letters); $i++) {
            $index = $index * 26 + (ord($letters[$i]) - ord('A') + 1);
        }
        return max(0, $index - 1);
    }
}
