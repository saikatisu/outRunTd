<?php


     // URL取得共通クラス
    class TreasureDataApi {

        const BASEURL = 'https://api.treasuredata.com/v3/';
        
        private $authorization;
        private $request;
        private $intervalTime;
        

        // 定義ファイルクラスのinstanceを指定
        public function __construct($log,$env) {
            
            $this->setAuthorization($env["Treasure"]["authorization"]);
            $this->request = new RequestClass($env,$log);
            $this->intervalTime = $env["api"]["retry_term"];
            
        }
        
        //認証情報をセット
        private function setAuthorization($authorization) {
            
            $this->authorization = "TD1 ".$authorization;
        }

        
        /* SQLを実行し、job_idを取得する
         * 
         * param: $sql,　$type,　$database
         * $sql： 実行するSQL
         * $type: 実行環境[presto,hive]
         * $database: デフォルトDBを選択する
         * 
         * return : Hash
         */
        public function postSql($sql,$type,$database,$format,$outFile) {
            
            try {
                //リクエストURL（SQL実行）
                $requestUrl = self::BASEURL . "job/issue/". $type ."/".$database;
                
                //ヘッダーを設定
                $headers = array(
                    "AUTHORIZATION:".$this->authorization,
                    "Content-Type: application/json"
                );
                
                //body部の設定
                $query = array(
                    'query' => $sql
                );
                
                //リクエスト結果(job_id)を取得
                $json=$this->request->get_request($requestUrl,$headers,$query);
                
                //ステータスを確認し、成功するまで待機する
                while (TRUE) {
                    
                    $status=$this->getStatus($json["job_id"]);
                    
                    if($status["status"]=="success"){
                        break;
                    }elseif ($status["status"]=="running"){
                        sleep($this->intervalTime);
                    }else{
                        throw new Exception("SQLの実行中にエラーが発生しました");
                    }
                    
                }
                
                //実行結果を取得する
                $this->getFile($json["job_id"] , $format ,$outFile);
                
                
            } catch (Exception $e) {
                
                throw $e;
                
            }
            

            
        }
        
        /* job_idのステータスを取得する
         *
         * param: $jobId
         *
         * return : Hash
         */
        private function getStatus($jobId) {
            try {
                //リクエストURL
                $requestUrl = self::BASEURL . "job/status/". $jobId;
                
                //ヘッダーを設定
                $headers = array(
                    "AUTHORIZATION:".$this->authorization,
                );
                
                //リクエスト結果(job_id)を取得
                return $this->request->get_request($requestUrl,$headers,null);
                
            } catch (Exception $e) {
                
                throw $e;
                
            }
        }
        
        /* job_idの結果をダウンロードする
         *
         * param: $jobId,$format
         *
         *  $jobid: 実行SQLのjobID
         *  $format: SQL結果のダウンロードフォーマット
         *
         * return : Hash
         */
        private function getFile($jobId , $format ,$outFile) {
            try {
                //リクエストURL
                $requestUrl = self::BASEURL . 'job/result/'. $jobId .'?format='.$format;
                
                //ヘッダーを設定
                $headers = array(
                    "AUTHORIZATION:".$this->authorization,
                );
                
                //リクエスト結果(job_id)を取得
                $this->request->get_request($requestUrl,$headers,null, $outFile);
                
                
            } catch (Exception $e) {
                
                throw $e;
                
            }
            
            
        }
        
     }

?>