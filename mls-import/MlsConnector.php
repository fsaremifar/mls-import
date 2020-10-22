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
    public $Username;
    /** @var string */
    public $Password;
    /** @var string */
    public $LoginUrl;
    /** @var string */
    public $Query;
    /** @var array */
    public $Classes;
    public function __construct()
    {
        

      

    }
    public function Load() {
       
        $file='wp-content/plugins/mls-import/config.json';
        $json = file_get_contents($file); 
        $result = MlsConfig::Deserialize($json);
        return result;
    }
}

class MlsConnector
{
    
    protected $config;
    protected $rets;
    protected $connection;
    protected $Configuration;

    
    	 
    public function __construct()
    {
        

       


    }
    public function Properties()
    {
        $this->Login();
        $result=[];
        $resource='Property'; 
        foreach ($this->Classes as $class) {
            
           
            writelog('Querying properties  Class:'. $class .' Query :'.$query);

            $retResults = $this->rets->Search(
                $resource,
                $class,
                $this->Query,
                [
                    'QueryType' => 'DMQL2',
                    'Count' => 0, // count and records 
                    'Limit' => 500, 
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
        $images = $this->rets->GetObject("Property", "HiRes",
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
            
            $config=$cfg->Load();
            $this->Configuration=$config;
            $this->Query=$config->Query;
            $this->Classes=explode(',',$config->Classes);
            $this->config = new \PHRETS\Configuration;
            
       

            $this->config->setLoginUrl($config->LoginUrl)
                ->setUsername($config->Username)
                ->setPassword($config->Password)
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
            writelog('Logging in to MLS with User'. $this->Configuration->Username);
            $this->connection = $this->rets->Login();
        }
    }
    public function Logout()
    {
        if($this->rets!=null)//check if rejected..

        {
            writelog('Logging out '. $this->Configuration->Username);

            $this->rets->Disconnect();
        }
    }
}
