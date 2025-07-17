<?php
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class Outputsheet{
    public static function ReportGenerator($reportname, $columns, $records)
    {
        #date_default_timezone_set('Africa/Nairobi');

        if (PHP_SAPI == 'cli')
            die('This example should only be run from a Web Browser');

        /** Include PHPExcel */

        // Create new phpspreadsheet object
        $spreadsheet = new Spreadsheet();

        // Set document properties
        $spreadsheet->getProperties()
            ->setCreator("permitflow")
            ->setTitle($reportname);

        // Add some data
        $spreadsheet->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
        $alpha_count = "B";
        foreach ($columns as $key => $value)
        {
            $spreadsheet->getActiveSheet()->getColumnDimension($alpha_count)->setAutoSize(true);
            $alpha_count++;
        }

        $spreadsheet->setActiveSheetIndex(0)->setCellValue('A4', 'No');
        $alpha_count = "B";
        foreach ($columns as $key => $value)
        {
            $spreadsheet->setActiveSheetIndex(0)->setCellValue($alpha_count.'4', $value);
            $alpha_count++;
        }

        $alpha_count--;

        $spreadsheet->getActiveSheet()->getStyle('A4')->getFont()->setBold(true);

        $spreadsheet->getActiveSheet()->getStyle('A4:'.($alpha_count).'4')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
        $spreadsheet->getActiveSheet()->getStyle('A4:'.($alpha_count).'4')->getFill()->getStartColor()->setARGB('46449a');

        $spreadsheet->getActiveSheet()->getStyle('A4')->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE);

        $alpha_count = "B";
        foreach ($columns as $key => $value)
        {
            $spreadsheet->getActiveSheet()->getStyle($alpha_count.'4')->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE);
            $alpha_count++;
        }

        $alpha_count--;

        $spreadsheet->getActiveSheet()->getStyle('A1:'.($alpha_count).'1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
        $spreadsheet->getActiveSheet()->getStyle('A1:'.($alpha_count).'1')->getFill()->getStartColor()->setARGB('504dc5');
        $spreadsheet->getActiveSheet()->getStyle('A2:'.($alpha_count).'2')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
        $spreadsheet->getActiveSheet()->getStyle('A2:'.($alpha_count).'2')->getFill()->getStartColor()->setARGB('504dc5');
        $spreadsheet->getActiveSheet()->getStyle('A3:'.($alpha_count).'3')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
        $spreadsheet->getActiveSheet()->getStyle('A3:'.($alpha_count).'3')->getFill()->getStartColor()->setARGB('504dc5');
        //get ap settings
        $q=Doctrine_Query::create()
            ->from('ApSettings s');
        $settings=$q->fetchOne();

        $objDrawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
        if($settings && $settings->getAdminImageUrl()){
            $objDrawing->setName($settings->getOrganisationName());
            $objDrawing->setDescription('Logo');
            $objDrawing->setPath('./'.$settings->getUploadDir().'/'.$settings->getAdminImageUrl());
        }else{
            $objDrawing->setName('Logo');
            $objDrawing->setDescription('Logo');
            $objDrawing->setPath('./assets_backend/images/logo.png');
        }
        $objDrawing->setHeight(60);
        $objDrawing->setWorksheet($spreadsheet->getActiveSheet());

    $alpha_count = "B";
        foreach ($columns as $key => $value)
      {
      $spreadsheet->getActiveSheet()->getStyle($alpha_count.'4')->getFont()->setBold(true);
      $alpha_count++;
    }

        /**
         * Fetch all applications linked to the filtered 'type of application' and the 'start date'
         */
        $count = 5;

    // Miscellaneous glyphs, UTF-8
    $alpha_count = "B";

    foreach($records as $record_columns)
    {
      $spreadsheet->setActiveSheetIndex(0)->setCellValue('A'.$count, $count-4);
      $alpha_count = "B";
      foreach ($record_columns as $key => $value)
      {
        $spreadsheet->setActiveSheetIndex(0)->setCellValue($alpha_count.$count, $value);
        $alpha_count++;
      }
      $count++;
    }


        // Rename worksheet
        $spreadsheet->getActiveSheet()->setTitle("Report");


        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $spreadsheet->setActiveSheetIndex(0);


        // Redirect output to a client’s web browser (Excel2007)
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'.$reportname.'.xlsx"');
        header('Cache-Control: max-age=0');

        $objWriter = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
        $objWriter->save('php://output');
        exit;
    }
    
}