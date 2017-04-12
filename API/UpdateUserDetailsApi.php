<?php

namespace ApiConnector;

class UpdateUserDetailsApi extends ApiConnector
{
    protected $requestName = 'UpdateUserDetails';
    
    public $olduseremail;
    public $newuseremail;
    public $loginType;
    public $givenname;
    public $familyname;
    public $dob;
    public $gender;
    public $phoneno;
    public $city;
    public $district;
    public $ownscc;
    public $bank;
    public $monthlyincome;
    public $nationalid;
    public $preferences = array();
    public $updatetoken;
    
    public function build_request_parameters()
    {
        return array('params' => 
                array (   'newuseremail' => $this->newuseremail
                        , 'useremail' => $this->olduseremail
                        , 'accounttype' => $this->loginType
                        , 'givenname' => $this->givenname
                        , 'familyname' => $this->familyname
                        , 'gender' => $this->gender
                        , 'dob' => $this->dob
                        , 'phoneno' => $this->phoneno
                        , 'accountid' => $this->nationalid
                        , 'bank' => $this->bank
                        , 'city' => $this->city
                        , 'district' => $this->district
                        , 'ownscc' => $this->ownscc
                        , 'monthlyincome' => $this->monthlyincome
                        , 'preferences' => $this->preferences
                        , 'updatetoken' => $this->updatetoken
                        
                )
        );
    }
}
