<?php

     class ReadConfig {

         private $envList;
        
        const XML = './env/envlist.xml'; 
        
        // 定義ファイル読込
        function __construct() {
     
            //xmlを読み込み
            $xml_obj= simplexml_load_file(self::XML);

            //xmlをjsonパース
            $xml_json = json_decode(json_encode( $xml_obj), true);
            
            //jsonをセット
            $this->setEnvList($xml_json);
            
        }
       
       
        private function setEnvList($json){
        	$this->envList = $json;
        }

        public function getEnvList() {
            return $this->envList;
        }
        
        
     }

?>