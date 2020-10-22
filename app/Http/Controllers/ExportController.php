<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PDF;
use ZipArchive;
use \RecursiveIteratorIterator;

class ExportController extends Controller
{
    public function showIndex(){
        return view('welcome');
    }

    public function export(Request $request){

        $csv = array_map('str_getcsv', file($request->attendee));

        $csv = array_slice($csv, 9);
        #dd($csv);

        $namesList = array();

        foreach($csv as $attendee){
            array_push($namesList, $attendee[2] . " " . $attendee[3]);
        }

        #dd($namesList);

        $uniqueNamesList = array_unique($namesList);

        $uniqueNamesList = array_slice($uniqueNamesList, 6);
        $uniqueNamesList = array_slice($uniqueNamesList, 0, -2);
        $uniqueNamesList = array_slice($uniqueNamesList, 0, -494);

        #dd($uniqueNamesList);

        ini_set('max_execution_time', 0);

        foreach($uniqueNamesList as $name){
            $pdf = PDF::loadView('pdf', ['name'=>$name]);
            $pdf->setPaper('A4', 'landscape');
            $pdf->save(public_path() . '/certificate/Mylan_Webinar_Certifikat_' . $name . '.pdf');
            #$path = file($pdf)->store('/public/cerrtificate');
        }

        $rootPath = realpath(public_path().'/certificate');
        $zip = new ZipArchive();
        $zip->open('file.zip', ZipArchive::CREATE | ZipArchive::OVERWRITE);
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($rootPath),
            RecursiveIteratorIterator::LEAVES_ONLY
        );
        
        foreach ($files as $name => $file)
        {
            // Skip directories (they would be added automatically)
            if (!$file->isDir())
            {
                // Get real and relative path for current file
                $filePath = $file->getRealPath();
                $relativePath = substr($filePath, strlen($rootPath) + 1);
        
                // Add current file to archive
                $zip->addFile($filePath, $relativePath);
            }
        }

        $zip->close();

        return $pdf->download('certificate.pdf');

        #$data = fgetcsv('/file/91453591787 - Attendee Report (1).csv');

    }

    public function showCertificate(){

        return view('pdf')->with(['name'=>'Haris Muslić']);
    }
}
