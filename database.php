<?php
/**
 * データベース接続クラス
 * 各自必要に応じて、$dsn、$user、$passの値を自分のDB設定に合わせて変更してください。
 */

//require $_SERVER['DOCUMENT_ROOT'] . '/modules/class/error.php';
require $_SERVER['DOCUMENT_ROOT'] . '/sd22_musicsite/modules/lib/util.php';

class database{
    private $dsn = 'mysql:host=127.0.0.1;port=3306;dbname=sd_master;charset=utf8;';//左から,ホスト名,ポート番号,DB名
    private $user = 'root';// ユーザー名
    private $pass = '';//パスワード
    public $dbh; //DBハンドラ
    public $stmt; //DBステートメント

    //コンストラクタ
    // public function __construct(){ 
        
    // }
 
    /**
     * PDOクラスのインスタンスを生成する
     */
    private function openPDO(){
        try{
            $this->dbh = new PDO(
                $this->dsn, $this->user, $this->pass,
                array(
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_EMULATE_PREPARES => false
                )
            );

        }catch (PDOException $e){
            print('接続エラー:'.$e->getMessage());
            die();
        }

        //display errors
        $this->dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
        return $this->dbh;
    }

    /**
     * PDOの後処理
     */
    private function closePDO(){
        $this->dbh = null;
        $this->stmt = null;
    }
      
    /**
     * 入力されたsqlを元にselect文を実行
     * @param string $query 入力されたsql文
     */
    public function select($query){
        $pdo = $this->openPDO();
        $stmt = $pdo->query($query);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $this->closePDO();
        return $result;
    }

    /**
     * 入力されたsqlをパラメータでinsert文を実行
     * @param string $table テーブル名
     * @param array $arrColumns 入力されたsql文
     * @param array $arrParams
     */  
    public function insert($table,$arrColumns,$arrParams){
        $pdo = $this->openPDO();

        $cntCol = count($arrColumns); //カラムの数
        $cntPar = count($arrParams); //パラメーターの数
        if(!$cntCol === $cntPar){
            $this->closePDO();
            echo 'not collect array.';
            exit();
        }
        $cntDepth = arrayDepth($arrParams); // 配列の深さ(多次元配列かどうか)
        $cntLimit = $cntCol-1;
        $lim2 = $cntPar-1;
        $place_holder = '';
        
        //クエリの生成
        $query = 'insert into '.$table.' (';
        for($i = 0;$i < $cntCol;++$i){
            $query .= $arrColumns[$i].',';
            $place_holder .= ':'.$arrColumns[$i].',';
            $arrBinders[] = ':'.$arrColumns[$i];
        }
        $place_holder = rtrim($place_holder,',');
        $query .= ') values ('.$place_holder.') ;';

        $stmt = $pdo->prepare($query);

        if($cntDepth == 1){
            for($i = 0;$i <= $cntLimit;$i++) {
                if (is_string($arrParams[$i])) {
                    $stmt->bindValue($arrBinders[$i], $arrParams[$i], PDO::PARAM_STR);
                } else if (is_int($arrParams[$i])) {
                    $stmt->bindValue($arrBinders[$i], $arrParams[$i], PDO::PARAM_INT);
                }
            }
            $stmt->execute();
        }else{
            for($i = 0;$i <= $lim2;$i++){
                for($j = 0;$j <= $cntLimit;$j++) {
                    if (is_string($arrParams[$i][$j])) {
                        $stmt->bindValue($arrBinders[$j], $arrParams[$i][$j], PDO::PARAM_STR);
                    } else if (is_int($arrParams[$i][$j])) {
                        $stmt->bindValue($arrBinders[$j], $arrParams[$i][$j], PDO::PARAM_INT);
                    }
                }
                $stmt->execute();
            }
        }
        $this->closePDO();
    }

    // public function delete(){

    // }

    // public function update(){

    // }
} 

