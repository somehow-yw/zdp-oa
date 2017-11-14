<?php
/**
 * Created by PhpStorm.
 * User: fer
 * Date: 2016/8/13
 * Time: 10:58
 */

namespace App\Utils;

class ExcelWriterUtil
{
    /**
     * EXCEL 数据下载
     *
     * @param string $fileName 需生成的文件名
     * @param array  $dataArr  导出的数据
     *
     * @return string
     */
    public static function excelDownload($fileName, $dataArr)
    {
        \Excel::create(
            $fileName,
            function ($excel) use ($dataArr) {
                $excel->sheet(
                    'table1',
                    function ($sheet) use ($dataArr) {
                        if ( ! empty($dataArr['format'])) {
                            if ( ! empty($dataArr['format']['columnType'])) {
                                // 设置列的格式 如：['C' => PHPExcel_Style_NumberFormat::FORMAT_TEXT]
                                $sheet->setColumnFormat($dataArr['format']['columnType']);
                            }
                        }
                        $sheet->fromArray($dataArr['data'], null, 'A1', false, false);
                        if ( ! empty($dataArr['format'])) {
                            if ( ! empty($dataArr['format']['columnMerge'])) {
                                // 按列合并行 如：['columns' => ['A', 'B', 'C',], 'rows' => [[1,2], ...]]
                                $sheet->setMergeColumn($dataArr['format']['columnMerge']);
                            }
                            if ( ! empty($dataArr['format']['cellsMerge'])) {
                                // 合并单元格 如：['A1:A5', 'B1:C1', ...]
                                foreach ($dataArr['format']['cellsMerge'] as $cellMergeVal) {
                                    $sheet->mergeCells($cellMergeVal);
                                }
                            }
                            if ( ! empty($dataArr['format']['setWidth'])) {
                                // 列宽的设置 格式如：['A' => 5, 'B' => 10, ...]
                                $sheet->setWidth($dataArr['format']['setWidth']);
                            }
                            if ( ! empty($dataArr['format']['setHeight'])) {
                                // 行高的设置 格式如：[1 => 50, 2 => 25, ...]
                                $sheet->setHeight($dataArr['format']['setHeight']);
                            }
                        }
                    }
                );
            }
        )->download('xls');

        return $fileName . '.xls';
    }
}