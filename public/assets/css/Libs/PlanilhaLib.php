<?php
namespace App\Libs;

use Exception;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Reader\Csv;
use PhpOffice\PhpSpreadsheet\Reader\Xls;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class PlanilhaLib
{
    public static function render($data, $filename = "planilha.xlsx") {
        if (empty($data)) {
            return [
                "error" => true,
                "message" => "Nenhum dado encontrado"
            ];
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Gerar cabeçalhos dinamicamente a partir da primeira linha do data
        $headers = array_keys($data[0]);
        $colIndex = 0;
        foreach ($headers as $coluna) {
            $colLetra = Coordinate::stringFromColumnIndex($colIndex + 1);
            $sheet->setCellValue("{$colLetra}1", $coluna);
            $sheet->getColumnDimension($colLetra)->setAutoSize(true);
            $colIndex++;
        }

        // Inserir os dados dinamicamente
        $row = 2;
        foreach ($data as $linha) {
            $colIndex = 0;
            foreach ($headers as $coluna) {
                $colLetra = Coordinate::stringFromColumnIndex($colIndex + 1);
                $valor = $linha[$coluna] ?? '';

                // Força como string para evitar perda de zeros à esquerda
                $sheet->setCellValueExplicit("{$colLetra}{$row}", $valor, DataType::TYPE_STRING);

                $colIndex++;
            }
            $row++;
        }

        // Enviar para download
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition: attachment;filename=\"{$filename}\"");
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;

    }

    // Função para importar arquivo Excel para MySQL
    public static function excelToArray(string $excelFile, bool $firstRowAsHeader = true): array {
        $excelFile = $_SERVER['DOCUMENT_ROOT'] . $excelFile;
        if (!file_exists($excelFile)) {
            return [
                "error" => true,
                "message" => "Arquivo não encontrado.",
                "data" => []
            ];
        }

        $fileExt = pathinfo($excelFile, PATHINFO_EXTENSION);
        if (!in_array(strtolower($fileExt), ['xls', 'xlsx', 'csv'])) {
            return [
                "error" => true,
                "message" => "Arquivo não é Excel válido.",
                "data" => []
            ];
        }

        try {
            if ($fileExt === 'csv') {
                $reader = new Csv();
            } elseif ($fileExt === 'xlsx') {
                $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
            } else {
                $reader = new Xls();
            }

            $spreadsheet = $reader->load($excelFile);
            $worksheet = $spreadsheet->getActiveSheet();
            $highestRow = $worksheet->getHighestDataRow();
            $highestColumn = $worksheet->getHighestDataColumn();
            $highestColumnIndex = Coordinate::columnIndexFromString($highestColumn);

            $data = [];
            $headers = [];

            if ($firstRowAsHeader) {
                for ($col = 1; $col <= $highestColumnIndex; $col++) {
                    $headers[] = mb_strtoupper((new FuncoesLib())->removeCaracteres(trim((string) $worksheet->getCell([$col, 1])->getValue())));
                }
                $startRow = 2;
            } else {
                for ($col = 1; $col <= $highestColumnIndex; $col++) {
                    $headers[] = mb_strtoupper("coluna_" . $col);
                }
                $startRow = 1;
            }

            for ($row = $startRow; $row <= $highestRow; $row++) {
                $rowData = [];
                for ($col = 1; $col <= $highestColumnIndex; $col++) {
                    $cellValue = trim($worksheet->getCell([$col, $row])->getFormattedValue());
                    $rowData[$headers[$col - 1]] = $cellValue;
                }
                $data[] = $rowData;
            }

            return [
                "error" => false,
                "message" => "Arquivo Excel convertido com sucesso. Total de " . count($data) . " registros.",
                "data" => $data,
                "headers" => $headers
            ];
        } catch (Exception $e) {
            return [
                "error" => true,
                "message" => "Erro ao processar o arquivo: " . $e->getMessage(),
                "data" => []
            ];
        }
    }
}
