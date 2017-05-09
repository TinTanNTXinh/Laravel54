<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Collection;
use DB;
use Excel;

class FileController extends Controller
{

    public function __construct()
    {
    }

    public function getImportExport()
	{
		return view('files.import-export');
	}

	public function getDownload($type)
	{
		$data = Collection::get()->toArray();
        foreach($data as $key => $item) {
            $data[$key]['Năm nay'] = $item['id'];
            unset($data[$key]['id']);
        }
//		return Excel::create('tintansoft_example', function($excel) use ($data) {
//			$excel->sheet('mySheet', function($sheet) use ($data)
//	        {
////				$sheet->fromArray($data);
////                $sheet->loadView('files.demo', array('data' => $data));
//
//                // first row styling and writing content
//                $sheet->mergeCells('A1:W1');
//                $sheet->row(1, function ($row) {
//                    $row->setFontFamily('Comic Sans MS');
//                    $row->setFontSize(30);
//                });
//
//                $sheet->row(1, array('Some big header here'));
//
//                // second row styling and writing content
//                $sheet->row(2, function ($row) {
//
//                    // call cell manipulation methods
//                    $row->setFontFamily('Comic Sans MS');
//                    $row->setFontSize(15);
//                    $row->setFontWeight('bold');
//
//                });
//
//                $sheet->row(2, array('Something else here'));
//
//                // getting data to display - in my case only one record
//
//                // setting column names for data - you can of course set it manually
//                $sheet->appendRow(array_keys($data[0])); // column names
//
//                // getting last row number (the one we already filled and setting it to bold
//                $sheet->row($sheet->getHighestRow(), function ($row) {
//                    $row->setFontWeight('bold');
//                });
//
//                // putting users data as next rows
//                foreach ($data as $user) {
//                    $sheet->appendRow($user);
//                }
//	        });
//        })->download($type);
        return Excel::create('Filename', function($excel) use ($data) {

            // Set the title
            $excel->setTitle('Our new awesome title');

            // Chain the setters
            $excel->setCreator('Maatwebsite')
                ->setCompany('Maatwebsite');

            // Call them separately
            $excel->setDescription('A demonstration to change the file properties');

            $excel->sheet('Sheetname', function($sheet) use ($data) {

                // Sheet manipulation
                $sheet->fromArray($data);

            });

        })->export($type);
	}

	public function postImport(Request $request)
	{
		if($request->hasFile('import_file')){
			$path = $request->file('import_file')->getRealPath();
			$data = Excel::load($path, function($reader) {
			})->get();
			if(!empty($data) && $data->count()){
				foreach ($data as $key => $value) {
					$insert[] = ['name' => $value->name, 'description' => $value->description];
				}
				if(!empty($insert)){
					DB::table('collections')->insert($insert);
					dd('Insert Record successfully.');
				}
			}
		}
		return back();
	}

	// Document Laravel-Excel
//        Loading a view for a single sheet
//        $sheet->loadView('folder.view');
//
//        Sharing a view for all sheets
//        Excel::shareView('folder.view')->create();
//
//        Unsetting a view for a sheet
//        $sheet->unsetView();
//
//        Passing variables to the view
//        As parameter
//        $sheet->loadView('view', array('key' => 'value'));
//        With with()
//        Using normal with()
//        $sheet->loadView('view')->with('key', 'value');
//        using dynamic with()
//        $sheet->loadView('view')->withKey('value');
}
