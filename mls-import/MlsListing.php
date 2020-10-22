<?php
class MlsListing
{

    protected $fields;
    protected $p;

    public $PropertyType;
    public $Key; 
    public $MLSID;
    public $ListDate;
    public $ExpirationDate;
    public $CloseDate;
    public $ContractDate;
    public $ListingStatus;
    public $OriginalListPrice; 
    public $PostId;
    public $ListPrice;
    public $ClosePrice;
    public $StreetNumber;
    public $StreetDirPrefix;
    public $StreetName;
    public $UnitNumber;
    public $StreetSuffix;
    public $City;
    public $StateOrProvince;
    public $County;
    public $PostalCode;
    public $Lat;
    public $Lon;
    public $TotalLiveableSqFt;
    public $TotalGarage;
    public $TotalParking;
    public $YearBuilt;
    public $LotSize;
    public $AprxAcres;
    public $IncorpArea;
    public $Mfg;
    public $Stories;
    public $Buildings;
    public $Bedrooms;
    public $BathsTotal;
    public $BathsFull;
    public $BathsHalf;
    public $BathsThreeQuarter;
    public $HOADuesDue;
    public $Zoning;
    public $Taxes;
    public $TaxYear;
    public $Subdivision;
    public $PublicRemarks;
    public $Remarks;
    public $TaxID;
    public $Directions;
    public $OwnersName;
    public $HOA;
    public $ResidentialStatus;
    public $Occupancy;

    public $Title;
    public $Address;
    public $Description; 

    public $ResidentialType;
    public $Images;
    public $Thumbnail;
    public function __construct($p)
    {
        // save the configuration along with this session
        $this->p = $p;
        
        $this->MLSID=$p['LIST_0'];
        $this->Key=$p['LIST_1'];
         
        $this->PropertyType=$p['LIST_8'];
        $this->ResidentialType=$p['LIST_9'];

        $this->ListDate=$p['LIST_10'];
        $this->ExpirationDate=$p['LIST_11'];
        $this->CloseDate=$p['LIST_12'];
        $this->ContractDate=$p['LIST_13'];
        $this->ListingStatus=$p['LIST_15'];
        $this->OriginalListPrice=$p['LIST_21'];
        $this->ListPrice=$p['LIST_22'];
        $this->ClosePrice=$p['LIST_23'];
        $this->StreetNumber=$p['LIST_31'];
        $this->StreetDirPrefix=$p['LIST_33'];
        $this->StreetName=$p['LIST_34'];
        $this->UnitNumber=$p['LIST_35 '];
        $this->StreetSuffix=$p['LIST_37'];
        $this->City=$p['LIST_39'];
        $this->StateOrProvince=$p['LIST_40'];
        $this->County=$p['LIST_41'];
        $this->PostalCode=$p['LIST_43'];
        $this->Lat=$p['LIST_46'];
        $this->Lon=$p['LIST_47'];
        $this->TotalLiveableSqFt=$p['LIST_48'];
        $this->TotalGarage=$p['LIST_51'];
        $this->TotalParking=$p['LIST_52'];
        $this->YearBuilt=$p['LIST_53'];
        $this->LotSize=$p['LIST_56'];
        $this->AprxAcres=$p['LIST_57'];
        $this->IncorpArea=$p['LIST_58'];
        $this->Mfg=$p['LIST_59'];
        $this->Stories=$p['LIST_64'];
        $this->Buildings=$p['LIST_65'];
        $this->Bedrooms=$p['LIST_66'];
        $this->BathsTotal=$p['LIST_67'];
        $this->BathsFull=$p['LIST_68'];
        $this->BathsHalf=$p['LIST_69'];
        $this->BathsThreeQuarter=$p['LIST_70'];
        $this->HOADuesDue=$p['LIST_73'];
        $this->Zoning=$p['LIST_74'];
        $this->Taxes=$p['LIST_75'];
        $this->TaxYear=$p['LIST_76'];
        $this->Subdivision=$p['LIST_77'];
        $this->PublicRemarks=$p['LIST_78'];
        $this->Remarks=$p['LIST_79'];
        $this->TaxID=$p['LIST_80'];
        $this->Directions=$p['LIST_82'];
        $this->OwnersName=$p['LIST_83'];
        $this->HOA=$p['LIST_88'];
        $this->ResidentialStatus=$p['LIST_94'];
        $this->Occupancy=$p['LIST_96'];  
 
        $this->Title=$this->GetTitle();
        $this->Address=$this->GetAddress();
        $this->Description=$this->GetDescription();
        $this->Images=array();

    }
    
    public function Get($key)
    {
        return $this->p[$this->fields[$key]];
    
    }
    public function GetTitle()
    {
        return  $this->StreetName.' '.
                $this->City .' '.
                $this->TotalLiveableSqFt.' Sq Ft '.
                $this->Bedrooms.' bed(s)';
    
    }
    public function GetAddress()
    {
        return  $this->StreetNumber.' '.
                $this->StreetDirPrefix .' '. 
                $this->StreetName .' '.
                $this->UnitNumber.' '.
                $this->StreetSuffix.' '.
                $this->City .' '. 
                $this->StateOrProvince .' '.
                $this->PostalCode; 
    
    }
    public function GetDescription()
    {
        return  $this->PublicRemarks;
    
    }
     
    public function GetMetaDataForPropety()
    {
      return  array( 
        "_wp_page_template"=>"default",
        "slide_template"=>"default",
        "inspiry_is_published"=>"yes",
        "REAL_HOMES_hide_property_advance_search"=>"0",
        "REAL_HOMES_add_in_slider"=>"yes",
        "inspiry_additional_fee"=>"0",
        "inspiry_property_tax"=>"0",
        "REAL_HOMES_sticky"=>"0",
        "REAL_HOMES_energy_class"=>"none",
        "REAL_HOMES_agent_display_option"=>"agent_info",
        "REAL_HOMES_gallery_slider_type"=>"thumb-on-right",
        "REAL_HOMES_change_gallery_slider_type"=>"0",
        "REAL_HOMES_property_map"=>"0",
        "REAL_HOMES_property_address"=> $this->Address,
        "REAL_HOMES_property_old_price"=>$this->OriginalListPrice,
    
        "REAL_HOMES_agents"=>54,
        "REAL_HOMES_agents"=>57,
        "REAL_HOMES_property_location"=> $this->Lat.','. $this->Lon,
        "REAL_HOMES_featured"=>"0",
        "REAL_HOMES_property_year_built"=> $this->YearBuilt,
        "REAL_HOMES_property_id"=> $this->MLSID,
        "mls_imported"=> $this->Key,
        "REAL_HOMES_property_garage"=> $this->TotalGarage,
        "REAL_HOMES_property_bathrooms"=> $this->BathsTotal,
        "REAL_HOMES_property_bedrooms"=> $this->Bedrooms,
        "REAL_HOMES_property_lot_size_postfix"=>"Sq Ft",
        "REAL_HOMES_property_lot_size"=> $this->LotSize,
        "REAL_HOMES_property_size_postfix"=>"Sq Ft",
        "REAL_HOMES_property_size"=> $this->TotalLiveableSqFt,
        "REAL_HOMES_property_price"=> $this->ListPrice,
        
        );
    }    
}

