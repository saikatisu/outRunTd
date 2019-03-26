<?php
    class LogClass {

        private $dir;
        private $file;

        /*
            コンストラクタ
            param: 対象ディレクトリ指定

        */
        function __construct($config){
            //環境変数を読み込み
            $this->dir = $config;
            $this->startRecode();
        }

        /*
            ログ記録開始処理
            param: null
            throw : Exception
        */
        private function startRecode() {

            try{

                date_default_timezone_set('Asia/Tokyo');
                $this->file = $this->dir. date('YmdHis').'.log';
                touch($this->file);
                $this->recode("ログの記録を開始します");

            }catch(Exception $e){
                throw $e;
            }

        }
        /*
            ログに記録する
            param: String
            throw : Exception
        */
        public function recode($str){
            try{
                $current = file_get_contents($this->file);
                $current .= "[" . date('Y年m月d日H時i分s秒') . "] ". $str ."\n";
                file_put_contents($this->file, $current);
            }catch(Exception $e){
                throw $e;
            }

        }
        /*
         ログの記録を終了する
         param: 1:正常終了　0:異常終了
         throw : Exception
         */
        public function endRecode($param){
            try {
                $end_file = "./success.txt";
                touch($end_file);
                file_put_contents($end_file, $param);
                
            } catch (Exception $e) {
                throw $e;
            }
            
        }
        
    }


?>