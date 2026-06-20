<?php

namespace App\Http\Controllers;

use App\Imports\BillImport;
use App\Imports\CustomerImport;
use App\Models\ImportLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class ImportController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = ImportLog::with('user');

        if (! $user->isAdmin()) {
            $query->where('user_id', $user->id);
        } elseif ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('date')) {
            $query->whereDate('import_date', $request->date);
        }
        if ($request->filled('type')) {
            $query->where('import_type', $request->type);
        }

        $recentLogs = $query->orderBy('import_date', 'desc')->orderBy('created_at', 'desc')->take(10)->get();

        $base = ImportLog::query();
        if (! $user->isAdmin()) {
            $base->where('user_id', $user->id);
        } elseif ($request->filled('user_id')) {
            $base->where('user_id', $request->user_id);
        }
        if ($request->filled('date')) {
            $base->whereDate('import_date', $request->date);
        }
        if ($request->filled('type')) {
            $base->where('import_type', $request->type);
        }

        $stats = [
            'total_imports' => (clone $base)->count(),
            'total_records' => (clone $base)->sum('total_rows'),
            'total_inserted' => (clone $base)->sum('inserted_rows'),
            'total_updated' => (clone $base)->sum('updated_rows'),
            'customer_imports' => (clone $base)->where('import_type', 'customers')->count(),
            'bill_imports' => (clone $base)->where('import_type', 'bills')->count(),
        ];

        $users = $user->isAdmin() ? User::orderBy('name')->get() : collect();

        return view('imports.index', compact('recentLogs', 'stats', 'users'));
    }

    public function history(Request $request)
    {
        $user = Auth::user();
        $query = ImportLog::with('user');

        if (! $user->isAdmin()) {
            $query->where('user_id', $user->id);
        } elseif ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('date')) {
            $query->whereDate('import_date', $request->date);
        }
        if ($request->filled('type')) {
            $query->where('import_type', $request->type);
        }

        $logs = $query->orderBy('import_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $base = ImportLog::query();
        if (! $user->isAdmin()) {
            $base->where('user_id', $user->id);
        } elseif ($request->filled('user_id')) {
            $base->where('user_id', $request->user_id);
        }
        if ($request->filled('date')) {
            $base->whereDate('import_date', $request->date);
        }
        if ($request->filled('type')) {
            $base->where('import_type', $request->type);
        }

        $stats = [
            'total_imports' => (clone $base)->count(),
            'total_records' => (clone $base)->sum('total_rows'),
            'total_inserted' => (clone $base)->sum('inserted_rows'),
            'total_updated' => (clone $base)->sum('updated_rows'),
            'customer_imports' => (clone $base)->where('import_type', 'customers')->count(),
            'bill_imports' => (clone $base)->where('import_type', 'bills')->count(),
            'completed_count' => (clone $base)->where('status', 'completed')->count(),
        ];

        $users = $user->isAdmin() ? User::orderBy('name')->get() : collect();

        return view('imports.history', compact('logs', 'stats', 'users'));
    }

    public function preview(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
            'import_type' => 'required|in:customers,bills',
            'import_date' => 'nullable|date',
        ]);

        $path = $request->file('file')->store('imports');

        if (!$path) {
            return response()->json(['error' => 'File upload failed.'], 500);
        }

        $tempPath = null;

        try {
            $ext = $request->file('file')->getClientOriginalExtension();
            $tempPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'import_' . uniqid() . '.' . $ext;

            $content = Storage::get($path);
            file_put_contents($tempPath, $content);

            $rows = $this->parseExcel($tempPath);
            $importType = $request->import_type;

            if (empty($rows)) {
                Storage::delete($path);
                return response()->json(['error' => 'Excel file is empty or has no data rows'], 422);
            }

            $importer = $importType === 'customers' ? new CustomerImport : new BillImport;
            $expected = $importer->expectedHeadings();

            $headings = array_keys($rows[0]);
            $headingsLower = array_map('strtolower', $headings);
            $expectedLower = array_map('strtolower', $expected);
            $missingHeadings = [];
            foreach ($expected as $i => $h) {
                if (!in_array($expectedLower[$i], $headingsLower)) {
                    $missingHeadings[] = $h;
                }
            }

            $validRows = [];
            $errorRows = [];
            $totalErrors = 0;

            foreach ($rows as $index => $row) {
                $rowErrors = $importer->validateRow($row, $index);
                if (!empty($rowErrors)) {
                    $errorRows[] = ['row' => $index + 2, 'data' => $row, 'errors' => $rowErrors];
                    $totalErrors++;
                } else {
                    $validRows[] = $row;
                }
            }

            session()->put('import_preview', [
                'path' => $path,
                'import_type' => $importType,
                'import_date' => $request->import_date ?? now()->toDateString(),
                'headings' => $headings,
                'valid_rows' => $validRows,
                'error_rows' => $errorRows,
                'total_rows' => count($rows),
                'valid_count' => count($validRows),
                'error_count' => $totalErrors,
            ]);

            return response()->json([
                'headings' => $headings,
                'expected_headings' => $expected,
                'missing_headings' => array_values($missingHeadings),
                'valid_rows' => array_slice($validRows, 0, 50),
                'error_rows' => $errorRows,
                'total_rows' => count($rows),
                'valid_count' => count($validRows),
                'error_count' => $totalErrors,
            ]);
        } catch (\Exception $e) {
            Storage::delete($path);
            return response()->json(['error' => 'Failed to parse file: ' . $e->getMessage()], 422);
        } finally {
            if ($tempPath && file_exists($tempPath)) {
                unlink($tempPath);
            }
        }
    }

    public function confirm(Request $request)
    {
        $preview = session()->get('import_preview');

        if (!$preview || empty($preview['valid_rows'])) {
            return response()->json(['error' => 'No preview data found. Please upload again.'], 422);
        }

        $path = $preview['path'];
        $importType = $preview['import_type'];
        $importDate = $preview['import_date'] ?? now()->toDateString();
        $validRows = $preview['valid_rows'];
        $totalRows = $preview['total_rows'];
        $headings = $preview['headings'] ?? [];

        $importer = $importType === 'customers' ? new CustomerImport : new BillImport;
        $userId = Auth::id();

        $sampleData = array_slice($validRows, 0, 50);

        $log = ImportLog::create([
            'file_name' => basename($path),
            'file_path' => $path,
            'import_type' => $importType,
            'import_date' => $importDate,
            'total_rows' => $totalRows,
            'inserted_rows' => 0,
            'updated_rows' => 0,
            'skipped_rows' => 0,
            'errors' => [],
            'imported_data' => [
                'headings' => $headings,
                'rows' => $sampleData,
                'total_valid' => count($validRows),
            ],
            'status' => 'processing',
            'user_id' => $userId,
        ]);

        $inserted = 0;
        $updated = 0;
        $skipped = 0;
        $errors = [];

        foreach ($validRows as $row) {
            try {
                $result = $importer->upsert($row, $userId, $importDate);
                if ($result['action'] === 'inserted') {
                    $inserted++;
                } elseif ($result['action'] === 'updated') {
                    $updated++;
                } else {
                    $skipped++;
                    if (!empty($result['reason'])) {
                        $errors[] = 'Row skipped: ' . ($result['reason'] ?? 'Unknown reason');
                    }
                }
            } catch (\Exception $e) {
                $skipped++;
                $errors[] = 'Row error: ' . $e->getMessage();
            }
        }

        $log->update([
            'inserted_rows' => $inserted,
            'updated_rows' => $updated,
            'skipped_rows' => $skipped,
            'errors' => count($errors) > 0 ? $errors : null,
            'status' => 'completed',
        ]);

        session()->forget('import_preview');

        Storage::delete($path);

        return response()->json([
            'success' => true,
            'total' => $totalRows,
            'inserted' => $inserted,
            'updated' => $updated,
            'skipped' => $skipped,
            'errors' => $errors,
        ]);
    }

    public function destroy(ImportLog $importLog)
    {
        $importLog->delete();
        return redirect()->back()->with('success', 'Import log deleted successfully.');
    }

    public function downloadSample($type)
    {
        if (!in_array($type, ['customers', 'bills'])) {
            abort(404);
        }

        $importer = $type === 'customers' ? new CustomerImport : new BillImport;
        $headings = $importer->expectedHeadings();

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();

        foreach ($headings as $colIndex => $heading) {
            $col = chr(65 + $colIndex);
            $sheet->setCellValue($col . '1', $heading);
            $sheet->getStyle($col . '1')->getFont()->setBold(true);
        }

        if ($type === 'customers') {
            $sheet->setCellValue('A2', 'John Doe');
            $sheet->setCellValue('B2', '01712345678');
            $sheet->setCellValue('C2', 'Dhaka');
        } else {
            $sheet->setCellValue('A2', 'BILL-001');
            $sheet->setCellValue('B2', 'John Doe');
            $sheet->setCellValue('C2', '01712345678');
            $sheet->setCellValue('D2', 'Shop Name');
            $sheet->setCellValue('E2', 'Bill Man');
            $sheet->setCellValue('F2', 1000);
            $sheet->setCellValue('G2', 0);
            $sheet->setCellValue('H2', 'cash');
            $sheet->setCellValue('I2', 1000);
        }

        foreach (range('A', chr(64 + count($headings))) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $filename = $type === 'customers' ? 'customer-import-sample.xlsx' : 'bill-import-sample.xlsx';
        $tempPath = storage_path('app/' . $filename);
        $writer->save($tempPath);

        return response()->download($tempPath, $filename)->deleteFileAfterSend(true);
    }

    private function parseExcel(string $filePath): array
    {
        $spreadsheet = IOFactory::load($filePath);
        $sheet = $spreadsheet->getActiveSheet();
        $data = $sheet->toArray(null, true, true, true);

        if (empty($data) || count($data) < 2) {
            return [];
        }

        $headings = array_map(function ($h) {
            return strtolower(trim(preg_replace('/[^a-zA-Z0-9_ ]/', '', $h)));
        }, array_values($data[1]));

        $headings = array_map(function ($h) {
            return str_replace(' ', '_', $h);
        }, $headings);

        $rows = [];
        for ($i = 2; $i <= count($data); $i++) {
            $row = [];
            $values = array_values($data[$i]);
            $isEmpty = true;

            foreach ($headings as $colIndex => $heading) {
                $value = $values[$colIndex] ?? null;

                $isDateField = str_contains($heading, 'date');
                if ($isDateField && is_numeric($value) && $value > 40000) {
                    try {
                        $value = Date::excelToDateTimeObject($value)->format('Y-m-d');
                    } catch (\Exception $e) {
                    }
                } elseif ($value instanceof \DateTimeInterface) {
                    try {
                        $value = $value->format('Y-m-d');
                    } catch (\Exception $e) {
                    }
                }

                $row[$heading] = $value !== null && $value !== '' ? $value : null;
                if ($value !== null && $value !== '') {
                    $isEmpty = false;
                }
            }

            if (!$isEmpty) {
                $rows[] = $row;
            }
        }

        return $rows;
    }
}
