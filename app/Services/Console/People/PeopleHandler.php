<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Services\Console\People;

use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

/**
 * Description of PeopleHandler
 *
 * @author KNovikov
 */
class PeopleHandler
{

    /**
     * 
     * @param string $separator
     * @return array
     */
    public function countAverageLineCount(string $separator): array
    {
        $people_files = $this->getFilesById($separator);
        $result = [];
        
        foreach ($people_files as $people_id => $people) {
            if (!empty($people['files'])) {
                $texts_count = count($people['files']);
                $line_count = 0;
                foreach ($people['files'] as $file) {
                    $path_to_people_text_file = Storage::disk('temp')->path($file);
                    $line_count += count(file($path_to_people_text_file));
                }
                $average_line_count = $line_count / $texts_count;
                $result[] = $people['name'] . ', Cреднее количество строк = ' . $average_line_count;
            }
        }
        return $result;
    }

    /**
     * 
     * @param string $separator
     * @return array
     */
    public function replaceDates(string $separator): array
    {
        $people_files = $this->getFilesById($separator);
        $pattern = '/(0[1-9]|[12][0-9]|3[01])[\/](0[1-9]|1[012])[\/]\d{2}/';
        $result = [];
        foreach ($people_files as $people_id => $people) {
            $replace_count = 0;
            if (!empty($people['files'])) {
                foreach ($people['files'] as $file) {
                    $content = Storage::disk('temp')->get($file);
                    if (preg_match_all($pattern, $content, $matches, PREG_SET_ORDER)) {
                        foreach ($matches as $found_date) {
                            $formatted_date = Carbon::parse($found_date[0])->format('d-m-Y');
                            $content = str_replace($found_date[0], $formatted_date, $content);
                            $replace_count++;
                        }
                    }
                    Storage::disk('temp')->put('output_texts/' . basename($file), $content);
                }
            }
            $result[] = $people['name'] . ', Количество замен = ' . $replace_count;
        }
        return $result;
    }

    /**
     * 
     * Данная ф-я отвечает за работу с данными
     * парсим csv и преобразуем данные в удобочитаемый массив
     * с ID пользователя его именем и файлами
     * @param string $separator
     * @return array
     */
    private function getFilesById(string $separator): array
    {
        $files = Storage::disk('temp')->files('texts');
        $path_to_people_file = Storage::disk('temp')->path('people.csv');
        $csvFile = file($path_to_people_file);
        $people_files = [];
        
        foreach ($csvFile as $line) {
            $people = \str_getcsv($line, $separator);
            $people_id = $people[0];
            $people_name = $people[1];
            $people_files[$people_id]['name'] = $people_name;
            
            foreach ($files as $file) {
                $filename = basename($file, '.txt');
                if ((int) \explode('_', $filename)[0] == $people_id) {
                    $people_files[$people_id]['files'][] = $file;
                }
            }
        }
        
        return $people_files;
    }

}
