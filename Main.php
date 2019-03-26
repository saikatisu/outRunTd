<?php

    include './php_sub_class/ReadConfig.php';
    include './php_sub_class/LogClass.php';
    include './php_sub_class/TreasureDataApi.php';
    include './php_sub_class/RequestClass.php';

    
    //環境変数読み込み
    $readConfig = new ReadConfig();
    $env = $readConfig->getEnvList();

    //ログ出力開始
    $log = new LogClass($env["log"]["dir"]);
    
    //インスタンス化
    $api = new TreasureDataApi($log,$env);
    
    try{
        
        // 連想配列の場合、配列に変換
        foreach($env["sqlList"]as $key => $value) {
            if (is_array($value) && array_values($value) !== $value) {
                $env["sqlList"] = array($key => array($value));
            }
        }
        
        //SQL数ループ処理
        foreach ($env["sqlList"]["sql"] as $sql) {
            
            //ファイル読み込み
            $content = file_get_contents($sql["file"]);
            
            //SQL実行
            $api->postSql($content, $sql["type"], $sql["database"],$sql["format"],$sql["outFile"]);
            
            
            
        }
   		
   		//TODO リクエストを送信する
   		
        

    }catch(Exception $e){
        echo '捕捉した例外: ',  $e->getMessage(), "\n";
        $log->recode('捕捉した例外: '.  $e->getMessage());
        //異常終了ログを生成
        $log->endRecode(0);

    }

?>