<?php
/**
 * Created by PhpStorm.
 * User: bruce
 * Date: 18/3/5
 * Time: 上午4:25
 */

namespace app\common\command;
use think\console\Command;
use think\console\Input;
use think\console\Output;
/**
 * 计划任务 DATE
 * @author bruce
 *
 */
class Test extends Command
{

    protected function configure(){
        $this->setName('Test')->setDescription('计划任务 Test');
    }

    protected function execute(Input $input, Output $output){
        $output->writeln('Date Crontab job start...');
        /*** 这里写计划任务列表集 START ***/

        $this->test();


        /*** 这里写计划任务列表集 END ***/
        $output->writeln('Date Crontab job end...');
    }

    private function test(){
        echo "test\r\n";
    }
}