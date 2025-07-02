<?php
namespace App\Controllers;

use App\Models\TransactionModel;

require_once APP_DIR . '/Lib/PHPExcelReader/Classes/PHPExcel/IOFactory.php';

/**
 * Handles transaction-related operations
 */
class TransactionsController extends Controller
{
    /**
     * Retrieves a list of all transactions
     * 
     * @return \App\Core\Response JSON response containing transaction list
     */
    public function index()
    {
        $transactionModel = new TransactionModel();
        $result = $transactionModel->getList();

        return $this->success($result);
    }

    /**
     * Handles transaction file upload and data import
     * 
     * @return \App\Core\Response JSON response with import status
     * @throws \Exception If file processing fails
     */
    public function upload()
    {
        if (!isset($_FILES['file'])) {
            return $this->error('No file uploaded');
        }

        $file = $_FILES['file'];

        $allowedTypes = ['text/csv', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'];
        if (!in_array($file['type'], $allowedTypes)) {
            return $this->error('Invalid file type');
        }

        $file = new \File();
        $filePath = $file->upload($_FILES['file']);

        $objPHPExcel = \PHPExcel_IOFactory::load($filePath);

        // toArray($nullValue = null, $calculateFormulas = true, $formatData = true, $returnCellRef = false)
        $sheetData = $objPHPExcel->getActiveSheet()->toArray(null,true,true,true);

        if (empty($sheetData)) {
            $this->error('Datasheet is empty');
        }

        $transactionModel = new TransactionModel();
        $transactionModel->truncate();

        $insertData = [];
        foreach ($sheetData as $index => $row) {
            // Begins from $index = 1
            if ($index < 2) continue;
            
            $insertData[] = [
                'account'    => $row['A'],
                'ident'      => $row['B'],
                'amount'     => $row['C'],
                'currency'   => $row['D'],
                'date'       => $row['E']
            ];
        }

        db()->insert('transactions', $insertData);

        // Update currency rates
        // @TODO Move to daily cron
        $currencies = array_unique(array_map(function($row) { return $row['currency']; }, $insertData));
        $rates = \App\Services\SNB::getRates($currencies);
        (new \App\Models\RatesModel())->saveRates($rates);
        // ---
        
        return $this->success([
            'message' => 'Transactions imported successfully',
            'count' => count($insertData)
        ]);
    }

    /**
     * Updates an existing transaction
     * 
     * @param int $id Transaction ID to update
     * @return \App\Core\Response JSON response indicating success
     */
    public function update($id)
    {
        $transactionModel = new TransactionModel();
        $transactionModel->update($id, app()->request()->post());

        $this->success();
    }

    /**
     * Deletes a transaction
     * 
     * @param int $id Transaction ID to delete
     * @return \App\Core\Response JSON response indicating success
     */
    public function delete($id)
    {
        $transactionModel = new TransactionModel();
        $transactionModel->delete((int)$id);

        $this->success();
    }
}