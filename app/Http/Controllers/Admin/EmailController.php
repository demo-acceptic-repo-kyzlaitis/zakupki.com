<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;

class EmailController extends Controller
{
    private $copyPrefix = 'copy-';

    public function index()
    {
        $shit = base_path('resources'.DIRECTORY_SEPARATOR.'views'.DIRECTORY_SEPARATOR.'emails');

        $files = File::allFiles($shit);

        return view('admin.pages.email.index', compact('files'));
    }



    public function updateTemplate(Request $request) {
        $data = $request->all();

        /** копирует файл с префиксом сopy шо бы в случае лажи всегда можно было откатиться*/
        $filePath = base_path('resources'.DIRECTORY_SEPARATOR.'views'.DIRECTORY_SEPARATOR.'emails').DIRECTORY_SEPARATOR.$request->input('filePath');
        $copyPath = base_path('resources'.DIRECTORY_SEPARATOR.'views'.DIRECTORY_SEPARATOR.'emails').DIRECTORY_SEPARATOR.$this->copyPrefix.$request->input('filePath');
        File::copy($filePath, $copyPath);


        /** заменяет контент файла */
        File::put($filePath, $data['content']);

        return response()->json([], 202);
    }

    public function restoreCopy(Request $request) {
        /** получаем контенет из коппи */
        $copyPath = base_path('resources'.DIRECTORY_SEPARATOR.'views'.DIRECTORY_SEPARATOR.'emails').DIRECTORY_SEPARATOR.$this->copyPrefix.$request->input('filePath');
        $filePath = base_path('resources'.DIRECTORY_SEPARATOR.'views'.DIRECTORY_SEPARATOR.'emails').DIRECTORY_SEPARATOR.$request->input('filePath');
        $content = File::get($copyPath);

        /** вставляем контент из копии шаблон который будет отправлятся */
        try {
            File::put($filePath, $content);
        } catch(FileNotFoundException $exception) {
            return response()->json([], 404);
        }

        return response()->json([], 202);
    }

}
