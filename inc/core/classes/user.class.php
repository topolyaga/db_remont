<?
//класс пользователей

class User {
	
	private $database;
	
	private $dblocation; //Сервер базы данных
	private $dbname; //Имя базы данных
	private $dbuser; //Пользователь базы данных
	private $dbpasswd; //Пароль


	public function __construct($dblocation, $dbname, $dbuser, $dbpasswd)
    {
		$this->dblocation=$dblocation;
		$this->dbname=$dbname;
		$this->dbuser=$dbuser;
		$this->dbpasswd=$dbpasswd;
		
        $this->connectDb($this->dbname, $this->dbuser, $this->dbpasswd, $this->dblocation);
    }
	 
    /**
     * Проверяет, авторизован пользователь или нет
     * Возвращает true если авторизован, иначе false
     * @return boolean 
     */
    public function isAuth() 
	{
        if (isset($_SESSION["user_is_auth"]))
		{ 
			//Если сессия существует, Возвращаем значение переменной сессии is_auth (хранит true если авторизован, false если не авторизован)
            return $_SESSION["user_is_auth"];
        }
        else return false; //Пользователь не авторизован, т.к. переменная is_auth не создана
    }
     
    /**
     * Авторизация пользователя
     * @param string $login
     * @param string $passwors 
     */
   	public function Auth($login, $passwors)
    {
        $passwors = hash('sha256', $passwors);

        $mysql = $this->database->prepare("SELECT id FROM `users` WHERE login=:login && pass_sha256=:password LIMIT 0,1");
        $mysql->execute(array('login' => $login, 'password' => $passwors));
        $result_auth = $mysql->fetch(PDO::FETCH_ASSOC);

        if ($result_auth['id'] != '' && $result_auth['id'] != 0) {
            //Если логин и пароль введены правильно, то устанавливаем значения для сессии

            //Делаем пользователя авторизованным
            $_SESSION["user_is_auth"] = true;

            $_SESSION["user_id"] = $result_auth['id'];

            return array("status" => "success");

        } else {
            //Логин и пароль не подошел
            $_SESSION["user_is_auth"] = false;

            return array("status" => "error", "text" => "Неправильный логин или пароль");
        }
    }

    /**
     * Метод возвращает информацию о пользователе
     */
    public function GetInfo()
	{
        if ($this->isAuth())
			{
                //Возращаем общую информацию
                $mysql = $this->database->prepare("SELECT * FROM `users` WHERE id=:user_id");
                $mysql -> execute(array('user_id'=>$_SESSION["user_id"]));
                $resultselect=$mysql->fetch(PDO::FETCH_ASSOC);

                return $resultselect;			}
			else
			{
				//если не авторизован возращаем 0 использвуем для гостя
				return 0;	
			}
    }
    
	/*******************
	*****
	*****	Метод производит выход пользователя
	*****
	********************/ 
     
    public function Out() 
	{
		//Очищаем сессию
        $_SESSION = array();
		//Уничтожаем
        session_destroy();
    }
	
	/*******************
	*****
	*****	Метод производит коннект с БД внутри класса
	*****
	********************/
	
	public function connectdb($db_name, $db_user, $db_pass, $db_host = "localhost")
    {
        try 
		{
            $this->database = new \pdo("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
        } 
		catch (\pdoexception $e) 
		{
            //echo "database error: " . $e->getmessage();
            die();
        }
		
        $this->database->query('set names utf8');

        return $this;
    }
}
?>