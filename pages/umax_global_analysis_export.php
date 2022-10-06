<?
    use Bitrix\Main\Loader;
    
    if (Loader::includeModule('umax.seoanalysis') && !\UmaxAnalysisDataManager::isDemoEnd()) {
        $tableName = json_decode($_POST['array'], true);
        $fileName = $_POST['name'];

        function filterCustomerData(&$str) {
            $str = preg_replace("/\t/", "\\t", $str);
            $str = preg_replace("/\r?\n/", "\\n", $str);
            if (strstr($str, '"'))
                $str = '"' . str_replace('"', '""', $str) . '"';
        }
        
        // File Name & Content Header For Download
        $file_name = $fileName . ".xls";
        header("Content-Disposition: attachment; filename=\"$file_name\"");
        header("Content-Type: application/vnd.ms-excel");
        
        //To define column name in first row.
        $column_names = false;
        // run loop through each row in $customers_data
        foreach ($tableName as $row) {
            if (!$column_names) {
                echo implode("\t", array_keys($row)) . "\n";
                $column_names = true;
            }
            // The array_walk() function runs each array element in a user-defined function.
            array_walk($row, 'filterCustomerData');
            echo implode("\t", array_values($row)) . "\n";
        }
        exit;
    }
?>