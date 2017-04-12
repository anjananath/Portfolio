<?php

	namespace Request;
	
	class UpdateUserDetails extends \SprookiEngine\Request
	{
		/** @required */
		protected $appid;
		
		/** @required */
		protected $deviceid;
		
		/** @required */
		protected $useremail;
		
		/** @required */
		protected $password;
		
		/** @required */
		protected $accounttype;
		
		/** @required */
		protected $givenname;
		
		/** @required */
		protected $familyname;
		
        protected $newuseremail;
		protected $newpassword;
		protected $gender;
		protected $dob;
		protected $phoneno;
		protected $preferences = array();
		
		protected $preValidators = array( '\Validator\UserExists' );
		
		protected function pre_process_init()
		{
			$initData = array( 'user' => \Model\EndUser::get_user_details_by_email_and_appid(   $this->useremail
																							  , $this->appid
																							  , $this->password
																							  , $this->accounttype));
				
			return $initData;
		}
		
		protected function process($initData = array())
		{
			$responseCode = \SprookiEngine\Response::RESPONSE_CODE_FAILURE;
			$data = array();
			$error = NULL;
				
			try
			{
				$user = $initData['user'];
				$user->givenName = $this->givenname;
				$user->familyName = $this->familyname;
				$user->dob = \SprookiEngine\Util\System::default_value($this->dob, NULL, \SprookiEngine\Util\System::TYPE_DATETIME);
				$user->phoneNo = $this->phoneno;
				$user->gender = \Model\Enum\EndUserGender::toValue($this->gender) ? \Model\Enum\EndUserGender::toValue($this->gender) : NULL;
				
				if (false == \SprookiEngine\Util\String::is_empty($this->newpassword))
				{
					$user->password = $this->newpassword;
				}
				
				foreach (array_keys($this->extendedParams) as $key)
				{
					if (isset($this->$key))
					{
						$detail = new \Model\ExtendedDetail( array(   'extendedDetailKeyId' => \Model\Enum\ExtendedDetails\Type::toValue($key)
																	, 'ownerType' => \Model\Enum\ExtendedDetails\OwnerType::END_USER
																	, 'value' => $this->$key));
						$user->add_extended_detail($detail);
					}
				}
				
				$user->update();
				$this->store_user_preferences($user->endUserId);
				$responseCode = \SprookiEngine\Response::RESPONSE_CODE_SUCCESS;
				
			}
			catch (\Exception $e)
			{
				\SprookiEngine\Util\System::log_error(__CLASS__, $e->getMessage().' in '.$e->getFile().' at line '.$e->getLine());
				$error = new \SprookiEngine\Error(\SprookiEngine\Error::UNABLE_TO_UPDATE_USER_DETAILS);
			}
			
			return new \SprookiEngine\Response(   $responseCode
												, $data
												, $error
												, $this->get_service_name()
												, $this->sessid
												, $this->get_application_id());
		}
		
		protected function store_user_preferences($userId)
		{
			$userPreferences = array();
			foreach ($this->preferences as $preferences)
			{
				foreach ($preferences as $preferenceStr => $status)
				{
					$preference = new \Model\EndUserPreference();
					$preference->endUserId = $userId;
					$preference->type = \Model\Enum\EndUserPreferenceType::toValue($preferenceStr);
					$preference->status = \Model\Enum\EndUserPreferenceStatus::toValue($status);
					$preference->store($this->appid);
					$userPreferences[$preferenceStr] = $preference;
				}
			}
			return $userPreferences;
		}
		
		public function get_user_email()
		{
			return $this->useremail;
		}
        
        public function get_new_user_email()
        {
            return $this->newuseremail;
        }
        
		protected function initialize_user_preferences($userId)
		{
			$userPreferences = array();
			foreach ($this->preferences as $preference)
			{
				foreach ($preference as $key => $status)
				{
					$userPreferences[\Model\Enum\EndUserPreferenceType::toValue($key)] = \Model\Enum\EndUserPreferenceStatus::toValue($status);
				}
			}
			
            $currentUserPreferences = \Model\EndUserPreference::get_preferences_by_app_id_and_user_id($this->get_application_id(), $userId);
            $defaultPreferences = array();
            foreach($currentUserPreferences as $preference)
            {
                $defaultPreferences[$preference->type] = $preference->status;
            }
            
			$preferences = \Model\Enum\EndUserPreferenceType::get_all();
			$returnPreferences = array();
			foreach ($preferences as $key => $value)
			{
				$preference = new \Model\EndUserPreference();
				$preference->endUserId = $userId;
				$preference->type = $key;
                if(array_key_exists($key, $userPreferences))
                {
                    $preference->status = $userPreferences[$key];
                }
                // if preferences are not set in request, return the current preferences
                elseif(array_key_exists($key, $defaultPreferences))
                {
                    $preference->status = $defaultPreferences[$key];
                }
                else
                {
                    $preference->status =  \Model\Enum\EndUserPreferenceStatus::ENABLE;
                }
				$preference->store($this->get_application_id());
				$returnPreferences[] = $preference;
			}
				
			return $returnPreferences;
		}
		
		public function get_dob()
		{
			return NULL == $this->dob ? NULL : new \DateTime($this->dob);
		}
		
		public function get_phone_no()
		{
			return $this->phoneno;
		}
	}