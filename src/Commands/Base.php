<?php
namespace Springjk\Commands;

use Springjk\Vpn\System;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;

class Base extends Command
{
    public $system;

    public function __construct()
    {
        parent::__construct();

        $this->system = System::create();
    }

    public function sortArray($array, $sort_key)
    {
        $sort_keys = [];

        foreach ($array as $key => $value) {
            $sort_keys[] = $value[$sort_key];
        }

        array_multisort($sort_keys, SORT_ASC, $array);

        return $array;
    }

    protected function showTable($table_data, $output)
    {
        $table = new Table($output);

        $table_title = array_keys($table_data[0]);

        foreach ($table_title as $key => $value) {
            $table_title[$key] = ucwords($value);
        }

        $table
            ->setHeaders($table_title)
            ->setRows($table_data);

        $table->render();
    }
}