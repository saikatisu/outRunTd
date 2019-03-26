<?php


    class RequestClass {

        private $env;
        private $log;

        /*　コンストラクタ
            param: 環境変数、LogClass
        */
        function __construct($env,$log) {
            $this->env = $env;
            $this->log = $log;
        }
        
        private function custom(){
            
            
        }

        /* リクエスト処理
            param: url(String)
            return: json(Array)
        */
        public function get_request($url, $headers ,$request=null ,$option=null){
            try{
                //curlインスタンス生成
                $curl = curl_init();
                
                //再リクエストを有効
                $loop =TRUE;
                $count=0;
                

                //転送用オプションを設定する
                curl_setopt($curl, CURLOPT_URL, $url);
                
                if($request){
                    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
                    curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($request)); // jsonデータを送信
                }else{
                    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "GET");
                }
                curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); // 証明書の検証を行わない
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);  // curl_execの結果を文字列で返す

                //プロキシ設定がTrueの場合
                if($this->env["proxy"]["flg"]==1){
                    $this->log->recode("プロキシ設定を開始します。");
                    curl_setopt($curl, CURLOPT_PROXYPORT, '8080');
                    curl_setopt($curl, CURLOPT_PROXY, $this->env["proxy"]["certification"]);
                    
                }
                
                
                //ヘッダ設定がTrueの場合
                if($headers){
                    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
                }
                
                
                if($option){
                    $fp = fopen($option ,"w");
                    curl_setopt($curl, CURLOPT_FILE, $fp);
                    curl_setopt($curl, CURLOPT_HEADER, 0);
                }
                
                //再リクエストループ処理
                while ($loop) {
                    
                    //リクエストを実施
                    $this->log->recode("リクエストを送信を開始します。");
                    $response = curl_exec($curl);
                    
                    if($response == FALSE){
                        throw new Exception("リクエスト実行に失敗しました");
                    }
    
                    //ステータス200の場合
                    if( (int)curl_getinfo($curl, CURLINFO_HTTP_CODE)==200 ){
    
                        $this->log->recode("レスポンス結果を受信しました");
                        $json = json_decode($response, true);
    
                        //cURL リソースを閉じ、システムリソースを解放します
                        curl_close($curl);
                        
                        //再リクエストを無効
                        $loop =FALSE;
    
                        
                        //json結果を返す
                        $this->log->recode("\n".var_export($json,true));
                        
                        
                        if($option){
                            fclose($fp);
                        }
                        
                        return $json;
                            
  
                    //400～500番台_HTTPステータスの場合
                    }elseif( (int)curl_getinfo($curl, CURLINFO_HTTP_CODE) >= 400 and (int)curl_getinfo($curl, CURLINFO_HTTP_CODE) < 600){
                        $this->log->recode("HTTPステータス：　".(int)curl_getinfo($curl, CURLINFO_HTTP_CODE) );
                        
                        if ($count++ === (int) $this->env["api"]["retry"]) {
                            //例外処理
                            $this->log->recode("リトライ回数の上限に達しました");
                            break;
                        }else{
                        	  //リトライ間隔
                                $this->log->recode($this->env["api"]["retry_term"] . "秒の待機をします。");
                                sleep($this->env["api"]["retry_term"]);
                                $this->log->recode($this->env["api"]["retry_term"] . "秒の待機時間が経過しました。");
		                        
		                        $this->log->recode($count . "回目のリクエストを実行します");
                        }

                        
                    }else{
                        $this->log->recode("HTTPステータス：　".(int)curl_getinfo($curl, CURLINFO_HTTP_CODE) );
                        throw new Exception("予期しないHTTPステータス");
                        
                    }
                }
                //リトライ回数上限の場合
                if($loop){
                    throw new Exception("再リクエストしましたがリクエストに失敗しました");
                }
            }catch(Exception $e){
                //cURL リソースを閉じ、システムリソースを解放します
                curl_close($curl);
                throw $e;
            }
        } 
    }

?>