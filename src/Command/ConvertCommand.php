<?php
namespace FKSE\CsvToMarkdown\Command;

use FKSE\Utilities\StringUtil;
use ForceUTF8\Encoding;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ConvertCommand
 *
 * @author Fridolin Koch <info@fridokoch.de>
 */
class ConvertCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('convert')
            ->setDescription('Convert CSV file to markdown')
            ->addArgument('csv-file', InputArgument::REQUIRED, 'CSV file to convert')
            ->addOption('delimiter', null, InputOption::VALUE_OPTIONAL, 'Set the field delimiter, default is ,', ',')
            ->addOption('enclosure', null, InputOption::VALUE_OPTIONAL, 'Set the field enclosure character, default is "', '"')
            ->addOption('escape', null, InputOption::VALUE_OPTIONAL, 'Set the escape character, default is \\', '\\')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        //get formatter
        $formatter = $this->getHelper('formatter');
        $file = $input->getArgument('csv-file');
        //check if file exists
        if (!file_exists($file)) {
            $output->writeln($formatter->formatBlock(['[Error]', 'File '.$file.' not found.'], 'error', true));

            return;
        }
        //get options
        $delimiter = $input->getOption('delimiter');
        $enclosure = $input->getOption('enclosure');
        $escape = $input->getOption('escape');
        //get content and fix encoding
        $content = file_get_contents($file);
        //remove windows line breaks
        $content = str_replace("\r", '', $content);
        //split lines
        $lines = explode("\n", $content);
        //output
        $columns = [];
        //loop over all lines and put them into columns
        foreach ($lines as $line) {
            $csv = str_getcsv($line, $delimiter, $enclosure, $escape);

            foreach ($csv as $key => $field) {
                $field = trim($field);

                $columns[$key][] = $field;
            }
        }
        $rows = [];
        //loop over columns
        foreach ($columns as $columnKey => $column) {
            //row id
            $rowId = 0;
            //get max strlen
            $max = StringUtil::maxStrlen($column)+1;
            //make columns equal length
            foreach ($column as $fieldKey => $field) {
                $rows[$rowId][$columnKey] = ' '.str_pad($field, $max, ' ', STR_PAD_RIGHT);
                $rowId++;
            }
        }
        $table = '';
        //loop over rows
        foreach ($rows as $row) {
            $line = '|' . implode(' | ', $row) . '|';

            if ($table == '') {
                $line .=  "\n" . str_repeat('-', strlen($line));
            }

            $table .= $line . "\n";
        }

        echo Encoding::toUTF8($table);
    }
}
