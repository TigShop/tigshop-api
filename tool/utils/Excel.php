<?php

namespace utils;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class Excel
{
    /**
     * 导出
     * @param array $fields 文件标题栏 ['姓名','性别','年龄']
     * @param string $file_name 文件名
     * @param array $data 数据 [['张三','男','18'],['李四','男','20']]
     * @return void
     * @throws \PHPExcel_Exception
     * @throws \PHPExcel_Reader_Exception
     * @throws \PHPExcel_Writer_Exception
     */
    public static function export(array $fields = [], string $file_name = '', array $data = []): void
    {
        error_reporting(E_ALL);
        if (!is_array($fields)) return;
        $objPHPExcel = new Spreadsheet();
        $objPHPExcel->getProperties()->setCreator("TigShop");
        $objPHPExcel->getProperties()->setLastModifiedBy("TigShop");
        $objPHPExcel->getProperties()->setTitle("TigShop");
        $objPHPExcel->getProperties()->setSubject("TigShop");
        $objPHPExcel->setActiveSheetIndex(0);
        $header_arr = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG'];
        //操作类
        $startRow = 1;
        if (!empty($fields)) {
            foreach ($fields as $k => $v) {
                $objPHPExcel->getActiveSheet()->SetCellValue($header_arr[$k] . $startRow, $v);
            }
            $startRow += 1;
        }
        if (empty($file_name)) $file_name = Time::getCurrentDatetime('Y-m-d H:i:s') . rand(100000, 999999) . ".xlsx";
        if (!stripos($file_name, '.xlsx')) $file_name .= '.xlsx';
        foreach ($data as $k => $v) {
            $col = 0;
            foreach ($v as &$item) {
                $objPHPExcel->getActiveSheet()->SetCellValue($header_arr[$col] . $startRow, $item);
                $col++;
            }
            $startRow++;
        }

        header('Content-Type:application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition:attachment;filename=' . $file_name);// MIME 协议的扩展
        header('Cache-Control:max-age=0');


        $objWriter = IOFactory::createWriter($objPHPExcel, 'Xlsx');
        $objWriter->save('php://output');
    }


    /**
     * 导入
     * @param $fileName
     * @param $start
     * @param $getHighestColumn
     * @return array
     */
    public static function import($fileName, $start = 2, $getHighestColumn = 'auto'):array
    {
        $spreadsheet = IOFactory::load($fileName);

        $worksheet = $spreadsheet->getActiveSheet();

        $highestRow = $worksheet->getHighestRow();
        $highestColumn = $getHighestColumn == 'auto' ? $worksheet->getHighestColumn() : $getHighestColumn;
        $data = [];
        for ($start; $start <= $highestRow; ++$start) {
            $rows = $worksheet->rangeToArray('A' . $start . ':' . $highestColumn . $start, null);
            $data[] = $rows[0];
        }
        return $data;
    }
}