<?php
abstract class JsonDeserializer
{
    /**
     * @param string|array $json
     * @return $this
     */
    public static function Deserialize($json)
    {
        $className = get_called_class();
        $classInstance = new $className();
        if (is_string($json))
            $json = json_decode($json);

        foreach ($json as $key => $value) {
            if (!property_exists($classInstance, $key)) continue;

            $classInstance->{$key} = $value;
        }

        return $classInstance;
    }
    /**
     * @param string $json
     * @return $this[]
     */
    public static function DeserializeArray($json)
    {
        $json = json_decode($json);
        $items = [];
        foreach ($json as $item)
            $items[] = self::Deserialize($item);
        return $items;
    }
}
class MlsConfig extends JsonDeserializer
{
    /** @var string */
    public $username;
    /** @var string */
    public $password;
    /** @var string */
    public $loginUrl;
    /** @var string */
    public $query;
    /** @var string */
    public $classes;
    public function __construct()
    {
        

      

    }
    public function Load() {
       
        $file='wp-content/plugins/mls-import/Config.json';
         

        $json = file_get_contents($file); 
        $data = json_decode($json, true); 
        foreach ($data AS $key => $value) $this->{$key} = $value;

        //$result = MlsConfig::Deserialize($json);
        $this->loginUrl= stripslashes($this->loginUrl);

        return $this;
    }
}

class MlsConnector
{
    
    protected $config;
    protected $rets;
    protected $connection;
     /** @var MlsConfig */
    protected $Configuration;
 
    
    	 
    public function __construct()
    {
        

       


    }
    public function Properties()
    {
        $this->Login();
        $result=[];
        $resource='Property'; 
        $clss=explode(',',$this->Configuration->classes);

        $qr=$this->Configuration->query;
        foreach ($clss as $class) {
            
           
            writelog('Querying properties  Class:'. $class .' Query :'.$qr);

            $retResults = $this->rets->Search(
                $resource,
                $class,
                $qr,
                [
                    'QueryType' => 'DMQL2',
                    'Count' => 0, // count and records 
                    'Limit' => 700, 
                    'StandardNames' =>0, // give system names
                    'RestrictedIndicator'=>'****',   
                ]
            ); 

            foreach ($retResults as $value)
            {
                $p= new MlsListing($value);
                $this->SetThumbnail($p);
                $this->SetImages($p);
                array_push($result,$p);
            }
           
            
          

        }

        return $result;

    }
    protected function SetThumbnail($p)
    {
        $thumbnail = $this->rets->
        GetPreferredObject("Property", "Photo",
	        $p->Key,1); 
	  
	    $p->Thumbnail=$thumbnail->getLocation();

    }
    protected function SetImages($p)
    {
        $images = $this->rets->GetObject("Property", "Photo",
        $p->Key,["*"],1);
         
        
        foreach ($images as $img) {
             
            $url=$img->getLocation();
            if($url==null)continue;
            array_push($p->Images,$url);
        }
        
    }
    protected function Login()
    {
        
        if($this->config==null)
        {
 
            $cfg=new MlsConfig;

             
            $this->Configuration=$cfg->Load();
            
            writelog(' Configuration :'.json_encode($this->Configuration));
           
            $this->config = new \PHRETS\Configuration;
            
       

            $this->config->setLoginUrl($this->Configuration->loginUrl)
                ->setUsername($this->Configuration->username)
                ->setPassword($this->Configuration->password)
                ->setHttpAuthenticationMethod('basic')
                ->setOption('disable_follow_location', false);
        }
       
            
        if($this->rets==null)
        {
              // get a session ready using the configuration
    	$this->rets = new \PHRETS\Session($this->config);

        }
      
        if($this->connection==null)//check if rejected..

        {
            writelog('Logging in to MLS with User'. $this->Configuration->username);
            $this->connection = $this->rets->Login();
        }
    }
    public function Logout()
    {
        if($this->rets!=null)//check if rejected..

        {
            writelog('Logging out '. $this->Configuration->username);

            $this->rets->Disconnect();
        }
    }
}
