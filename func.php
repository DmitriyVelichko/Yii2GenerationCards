<?php
/*
 * storage - личный баланс (лична¤ карта и (или) наличные
 * card - общаяя карта
 * share Balance - личный баланс каждого + обща¤ карта
 * transfer - перевод с ЛС на общую карту это не расход и ни доход. Переводы нужно считать отдельно.
 */

//ini_set('error_reporting', E_ALL);
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);


interface iBalance
{
    public function editBalance($type, $sum, $comment, $purse);

    public function getBalance();
}

class Helper
{
    public static function display($message)
    {
        echo $message . ' ';
        die(__CLASS__);
    }
	
	public static function memory($text){
		// $memory = (!function_exists('memory_get_usage')) ? '' : round(memory_get_usage()/1024/1024, 2) . 'MB';
		// $time = date('H:i:s');
		// echo $text.' '.$time.' '.$memory."\n<br>";
	}

	public static function debug($data){
        echo '<pre>';
        var_dump($data);
        die;
    }
}

class DB
{
    private static $__connect = null;

    private $__connectParams = ['dsn' => 'mysql:dbname=;host=localhost;setchar',
        'user' => '',
        'password' => ''];

    private function __construct()
    {
        try {
            $dbh = new PDO($this->__connectParams['dsn'], $this->__connectParams['user'], $this->__connectParams['password']);
            $dbh->exec("set names utf8");
        } catch (PDOException $e) {
            echo 'Ошибка подключения: ' . $e->getMessage();
            die;
        }
        return $dbh;
    }

    protected function getDb()
    {
        if (!isset(self::$__connect)) {
            self::$__connect = $this->__construct();
        }
        return self::$__connect;
    }
}

/**
 * Ѕазовый класс балансов
 * Class Balance
 */
class Balance extends DB implements iBalance
{
    protected $tableName = null;
    protected $__tables = [
        'freer' => 'masha',
        'masha' =>'freer'
    ];

    const ACTIONS = [
        'add' => '+',
        'expense' => '-',
        'transfer' => '-'
    ];

    /**
     * @param $action add or expense
     * @param $sum
     * @param null $comment
     * @return mixed
     */
    public function editBalance($action, $sum, $comment, $purse)
    {
		Helper::memory(__FUNCTION__);
        $currentBalance = $this->getBalance();
        $str = $currentBalance[$purse] . Balance::ACTIONS[$action] . $sum;
        eval('$newBalance=' . $str . ';');

        $fields = $action . "_money,date_time, comment," . $purse;
        $query = "INSERT INTO " . $this->tableName . " ($fields) 
                  VALUES ($sum,NOW(),'$comment',$newBalance)";

        $result = $this->getDb()->prepare($query);
        return $result->execute();
    }

    public function getBalance()
    {
        $secondTable = $this->__tables[$this->tableName];
        $stm = $this->getDb()->prepare("SELECT s.storage ,
                                               (CASE
                                                 WHEN c.date_time > fcard.date_time
                                                  THEN c.card
                                                  ELSE fcard.card
                                                END) AS card
                                        FROM $this->tableName s , $secondTable c, $this->tableName fcard
                                        WHERE s.storage IS NOT NULL AND c.card IS NOT NULL AND fcard.card IS NOT NULL
                                        ORDER BY s.id DESC, c.id DESC, fcard.id DESC LIMIT 1");

        $stm->execute();
        $row = $stm->fetch(PDO::FETCH_ASSOC);
        return $row;
    }
}

class TransferBalance extends Balance{
    public function __construct($tableName)
    {
        $this->tableName = $tableName;
    }

    public function editBalance($action, $sum, $comment, $purse = null)
    {
        $currentBalance = $this->getBalance();

        $storage = $currentBalance[SingleBalance::OBJ] . Balance::ACTIONS[$action] . $sum;

        $card = $currentBalance[CardBalance::OBJ] . '+' . $sum;

        eval('$newSingleBalance=' . $storage . ';');
        eval('$newCardBalance=' . $card . ';');

        $fields = $action . "_money,date_time, comment," . SingleBalance::OBJ.','.CardBalance::OBJ;
        $query = "INSERT INTO " . $this->tableName . " ($fields) 
                  VALUES ($sum,NOW(),'$comment',$newSingleBalance,$newCardBalance)";

        $result = $this->getDb()->prepare($query);
        return $result->execute();

    }
}

/**
 * Ѕаланс на личных счетах
 * Class SingleBalance
 */
class SingleBalance extends Balance
{

    const OBJ = 'storage';

    /**
     * @param null $tableName им¤ пользовател¤
     */
    public function __construct($tableName)
    {
        $this->tableName = $tableName;
    }

    public function editBalance($action, $sum, $comment, $purse = null)
    {
        return parent::editBalance($action, $sum, $comment, self::OBJ);

    }
}

/**
 * Ѕаланс на обща¤ карта
 * Class CardBalance
 */
class CardBalance extends Balance
{
    const OBJ = 'card';

    public function __construct($tableName)
    {
        $this->tableName = $tableName;
    }

    /*
     * —перва получаем текущий личный баланс по карте, затем измен¤ем и добавл¤ем запись
     */
    public function editBalance($action, $sum, $comment, $purse = null)
    {
        return parent::editBalance($action, $sum, $comment, self::OBJ);

    }
}

/**
 * ќбщедоступные средства
 * Class ShareBalance
 */
class ShareBalance extends Balance
{

    public function __construct()
    {
    }

    public function getBalance()
    {
        $query = "SELECT (SELECT masha.storage
			FROM masha
			WHERE masha.storage IS NOT NULL
			ORDER BY id DESC
			LIMIT 1)
			+
			(SELECT freer.storage
			FROM freer
			WHERE freer.storage IS NOT NULL
			ORDER BY id DESC
			LIMIT 1)
			+
			(SELECT card
			from (
				(SELECT masha.date_time, masha.card FROM masha 
				WHERE masha.card IS NOT NULL ORDER BY masha.id DESC LIMIT 1)
				union all
				(SELECT freer.date_time, freer.card FROM freer WHERE freer.card IS NOT NULL ORDER BY freer.id DESC LIMIT 1)
			) un
			ORDER BY date_time DESC LIMIT 1) share";
        $result = $this->getDb()->prepare($query);
        $result->execute();
        $row = $result->fetch(PDO::FETCH_ASSOC)['share'];

        return $row;
    }
}

class Currency extends Balance
{
    public function __construct()
    {
    }

    public function getBalance()
    {
        $query = "SELECT
                   (CASE
                     WHEN m_euro.date_time > f_euro.date_time
                      THEN m_euro.euro
                      ELSE f_euro.euro
                    END) AS euro,
                   (CASE
                      WHEN m_dol.date_time > f_dol.date_time
                        THEN m_dol.dollar
                      ELSE f_dol.dollar
                     END) AS dollar
            
            FROM masha m_euro, freer f_euro, masha m_dol, freer f_dol
            WHERE m_euro.euro IS NOT NULL AND f_euro.euro IS NOT NULL AND m_dol.dollar IS NOT NULL AND f_dol.dollar IS NOT NULL
            ORDER BY m_euro.id DESC, f_euro.id DESC, m_dol.id DESC, f_dol.id DESC LIMIT 1;";
        $result = $this->getDb()->prepare($query);
        $result->execute();
        $row = $result->fetch(PDO::FETCH_ASSOC);

        return $row;
    }
}

class Stat extends DB
{
    private $__endDate;
    private $__firstDate;
	
    public function __construct($month = null)
    {
		if(!empty($month)){
			$currentDate = date('Y-').$month;
		}else{
			$currentDate = date('Y-m');
		}
		$this->__firstDate = date('Y-m-d H:i:s', strtotime($currentDate.'-01 00:00:01'));
		$this->__endDate   = $currentDate.'-'.date("t",strtotime($this->__endDate)).' 23:59:59';
    }

    public function getMyStatByUser($user){
        $query = "SELECT $user.*
                    FROM $user
                    WHERE $user.date_time BETWEEN '$this->__firstDate' AND '$this->__endDate'";
			
        $result = $this->getDb()->prepare($query);
        $result->execute();

        $data = [];
        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            $data[] = $row;
        }

        return $data;
    }

    public function getShareStat(){
        $query = "select *, 'freer' as 'own'
                    FROM freer
                    WHERE freer.date_time BETWEEN '$this->__firstDate' AND '$this->__endDate'
                    UNION
                    select *, 'masha' as 'own'
                    FROM masha
                    WHERE masha.date_time BETWEEN '$this->__firstDate' AND '$this->__endDate'";

        $result = $this->getDb()->prepare($query);
        $result->execute();

        $data = [];
        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            $data[] = $row;
        }

        return $this->__sortByDate($data);
    }

    private function __sortByDate($stat){
        foreach ($stat as $key1=>$value1){
            foreach ($stat as $key2=>$value2){
                if($stat[$key1]['date_time'] < $stat[$key2]['date_time']){
                    $temp = $stat[$key2];
                    $stat[$key2] = $stat[$key1];
                    $stat[$key1] = $temp;
                }
            }
        }
        return $stat;
    }
}

class User
{
    private $__currentUser;
    private $__secondUser;

    const ACCESS = [
        'freer' => '',
        'masha' => '',
    ];

    public function __construct()
    {
        $this->__currentUser = $_SESSION['USER_AUTH'];
        $temp = self::ACCESS;
        unset($temp[$this->__currentUser]);
        $this->__secondUser = array_keys($temp);
        $this->__secondUser = array_shift($this->__secondUser);
    }

    public function getCurrentUserName()
    {
        return $this->__currentUser;
    }

    public function getSingleBalance()
    {
		Helper::memory(__FUNCTION__);
        $singleBalance = new SingleBalance($this->__currentUser);
		$a = $singleBalance->getBalance();
		Helper::memory('END '.__FUNCTION__);
        return $a;
    }

    public function getShareBalance()
    {
		Helper::memory(__FUNCTION__);
        $shareBalance = new ShareBalance();
        $a = $shareBalance->getBalance();
		Helper::memory('END '.__FUNCTION__);
		return $a;
    }

    public function getCurrencyBalance()
    {
		Helper::memory(__FUNCTION__);
        $currencyBalance = new Currency();
        $a = $currencyBalance->getBalance();
		Helper::memory('END '.__FUNCTION__);
		return $a;
    }

    /**
     * »змен¤ет баланс на карте
     */
    public function editCardBalance($params)
    {
		Helper::memory(__FUNCTION__);
        $card = new CardBalance($this->__currentUser);
        return $card->editBalance($params['action'], $params['sum'], $params['comment']);
    }

    /**
     * »змен¤ет баланс на личном счете
     */
    public function editStorageBalance($params)
    {
        if($params['action'] == 'transfer'){
            $balance = new TransferBalance($this->__currentUser);
        }else{
            $balance = new SingleBalance($this->__currentUser);
        }

        return $balance->editBalance($params['action'], $params['sum'], $params['comment']);
    }

    /**
     * получает статискику по текущему пользователю
     * @return array
     */
    public function getMyStat($month = null){
		Helper::memory(__FUNCTION__);
        $stat = new Stat($month);
        $a = $this->__prepareForDisplay($stat->getMyStatByUser($this->__currentUser));
		Helper::memory('END '.__FUNCTION__);
		return $a;
    }

    /**
     * Получает статистику по всем пользователям
     * @return array
     */
    public function getShareStat($month = null){
        $stat = new Stat($month);
        return $this->__prepareForDisplay($stat->getShareStat());
    }

    private function __prepareForDisplay($data)
    {
        $rows = [];
		$n = '<br>';
        if (!empty($data)) {
            $shareSum = 0;
            $shareExpense = 0;
            $trasfers = 0;
            foreach ($data as $row) {
                $tempStr = '';

                // freer or masha
                if(isset($row['own'])){
                    $tempStr = '<i>'.$row['own'].'</i>: ';
                }
                // action & sum
                if (!empty($row['add_money'])) {
                    $tempStr .= '<span class="green">Пополнение <b>' . number_format($row['add_money'] ,0,'',' '). '</b> ₽</span> ';
                    $shareSum += $row['add_money'];
                } elseif(!empty($row['transfer_money'])){
                    $tempStr .= '<span class="blue-text">Перевод на Tinkoff <b>' . number_format($row['transfer_money'] ,0,'',' '). '</b> ₽</span> ';
                    $trasfers += $row['transfer_money'];
                }else {
                    $tempStr .= '<span class="red">Расход <b>' . number_format($row['expense_money'] ,0,'',' '). '</b> ₽</span> ';
                    $shareExpense += $row['expense_money'];
                }
                // obj
                if (!empty($row['storage'])) {
                    $tempStr .= 'личного счета ';
                } else {
                    $tempStr .= 'Tinkoff ';
                }
                // comment
                $tempStr .= $row['comment'].'<br>'.date('d.m.Y H:i:s',  strtotime($row['date_time']));
                $rows[] = $tempStr.$n;
            }

            $rows[] = '<span class="green">Итоговый доход: <b>'.$n.number_format($shareSum,0,'',' ').'</b> ₽</span>'.$n;
            $rows[] = '<span class="blue-text">Сумма переводов на Tinkoff: <b>'.$n.number_format($trasfers,0,'',' ').'</b> ₽</span>'.$n;
            $rows[] = '<span class="red">Итоговый расход: <b>'.$n.number_format($shareExpense,0,'',' ').'</b> ₽</span>'.$n;
        }
        return $rows;
    }
}

/* фабрика дл¤ выполнени¤ действий над кошельками */

class FactorySwitcher
{
    public static function editBalance($params)
    {
        $result = false;
        if (in_array($params['action'], array_keys(Balance::ACTIONS)) && isset($params['sum'])) {
            $params['comment'] = isset($params['comment']) ? $params['comment'] : null;
            $user = new User();
            switch ($params['obj']) {
                // баланс карты
                case 'card':
                    $result = $user->editCardBalance($params);
					Helper::memory('Edit complete');
                    break;

                // личный счет
                case 'storage':
                    $result = $user->editStorageBalance($params);
                    break;
            }
        }else{
            $result = ['error'=>__FUNCTION__];
        }
        return $result;
    }
}

function getDataFromUser()
{
	Helper::memory(__FUNCTION__);
    $user = new User();
    return ['single'     => $user->getSingleBalance(),
            'share'      => $user->getShareBalance(),
            'currency'   => $user->getCurrencyBalance(),
            'myStat'     => $user->getMyStat(),
            'shareStat'  => $user->getShareStat(),
            ];
}

// сценарий
session_start();
if(isset($_POST['login'])){
    // проверка данных и сохранение куков если верные
    $uName = $_POST['uName'];
    if(array_key_exists($uName, User::ACCESS) && User::ACCESS[$uName]==$_POST['uPass']){
        // сохраняем куку и делаем перезагрузку.
        $_SESSION['USER_AUTH'] = $uName;
    }else{
        echo '<h1>Ошибка доступа <a class="btn btn-sm" href="/index.php">попробовать снова</a></h1>';
        die;
    }
}else{
    // проверяем авторизацию
    if(!isset($_SESSION['USER_AUTH'])){
        echo '<h1>Ошибка доступа <a class="btn btn-sm" href="/index.php">попробовать снова</a></h1>';
        die;
    }
}

Helper::memory('START');
// если был получен ajax запрос
if (isset($_POST['action'])){
    if (FactorySwitcher::editBalance($_POST)) {
		Helper::memory('Getting new data');
        $data = getDataFromUser();
		Helper::memory('Return data');
        //обновляем данные на странице
        echo json_encode($data);
        die;
    }
} elseif(isset($_POST['month'])){
	$user = new User();
	$data = [
			'myStat'     => $user->getMyStat($_POST['month']),
            'shareStat'  => $user->getShareStat($_POST['month']),
			];
	echo json_encode($data);die;
}else{
    $data = getDataFromUser();
}