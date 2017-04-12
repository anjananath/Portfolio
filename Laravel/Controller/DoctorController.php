<?php
class DoctorController extends BaseController
{
        function __construct()
        {           
            $this->beforeFilter(function()
            {
                if(Auth::check()!=true)
                {          
                    $msg='';
                    return View::make('login.doctor_login')->with('error',$msg);
                }
            },array('except' => array('getIndex','postCheck','getLogin','anyShowSignup','anyRegister','anyCreateDoctor','anyFinalRegister','doctorSignup')));                
        }
        
        public function getIndex() 
        {
            return Redirect::action('DoctorController@getLogin',array());
        } 
        
//      ---show signup page---
        public function anyShowSignup()
        {
            return View::make('signup.doctor_signup');
        }
        
        
//      ---goto speciality fill up page
        public function anyCreateDoctor() 
        {   
            if((Input::get('other_speciality')=='')&&(Input::get('example')==''))
            {
                $rules = array(
                    'user_email_id'   => array('required', 'unique:users'),    
                    'profile_name' => 'required',
                    'gender'   =>'required',
                    'qualification' => 'required',
                    'example' => 'required',
                    'city' => 'required',
                    'category' => 'required',
                    'doctor_profile_picture_path' => 'required',
                    'doctor_contact_no' => 'required|numeric|digits_between:10,10'
        	 );
                //'special_description' => 'required',
            }
	else if((Input::get('other_location')=='')&&(Input::get('city')=='')){
                $rules = array(
                    'user_email_id'   => array('required', 'unique:users'),    
                    'profile_name' => 'required',
                    'gender'   =>'required',
                    'qualification' => 'required',
                    'example' => 'required',
                    'city' => 'required',
                    'category' => 'required',
                    'doctor_profile_picture_path' => 'required',
                    'doctor_contact_no' => 'required|numeric|digits_between:10,10'
        	 );
            }
            else 
            {
                $rules = array(
                                'user_email_id'   => array('required', 'unique:users'),    
                                'profile_name' => 'required',
                                'gender'   =>'required',
                                'qualification' => 'required',
                                'category' => 'required',
                                'doctor_profile_picture_path' => 'required',
                                'doctor_contact_no' => 'required|numeric|digits_between:10,10'
                            );
                //'special_description' => 'required',
            }
            
            $messages = array(
                'doctor_contact_no.digits_between' => 'Please enter valid phone number'
            );
            
           $validator = Validator::make(Input::all(), $rules, $messages);
           if ($validator->passes()) 
           {
                        $user_email_id = Input::get('user_email_id');
                        $user = new User(array('user_email_id'=> $user_email_id,'user_type' => 0));
                        $user->save();
                        
                        $spec_tags = array(); $new_spec_tags = '';
			 $new_location ='';
                        if(Input::get('example'))
                        {
                            $spec_tags = Input::get('example');
                        }
                        if(Input::get('other_speciality'))
                        {
                            $new_spec_tags = Speciality::insertGetId(array('speciality_name' => Input::get('other_speciality'),'speciality_content' => Input::get('special_description_new'),'status'=> 0));
                        }
                        if(Input::get('other_location')){
                            $new_location = Location::insertGetId(array('location_name' => Input::get('other_location'),'location_status' => 1));
                        }
                        $data['user_id']=$user->user_id;
                        $data['doctor_profile_name']=Input::get('profile_name');
                        $data['doctor_gender']=Input::get('gender');
                        $data['doctor_qualification']=Input::get('qualification');
                        //$data['doctor_city_id']=Input::get('city');
			if( $new_location!=''){
                            $citynew = Location::where('location_name','=',Input::get('other_location'))->first();
                            $data['doctor_city_id'] = $citynew->location_id;
                        }
                        else{
                           $data['doctor_city_id']=Input::get('city'); 
                        }
                        $data['category']=Input::get('category');
                        $data['doctor_contact_no']=Input::get('doctor_contact_no');
                        
                        if (Input::hasFile('doctor_profile_picture_path'))
                        {                        
                            $file = Input::file('doctor_profile_picture_path');
                            $file_org = $file->getClientOriginalName();
                            $file_name = Str::random(20).'_'.$file_org;

                            $destinationPath = 'images/upload/';
                            $destinationPath1 = 'images/resize_small/';
                            $destinationPath2 = 'images/resize_large/';


                            $uploadSuccess = Input::file('doctor_profile_picture_path')->move($destinationPath, $file_name);
                            $file_path = $destinationPath.$file_name;
                            $file_path1 = $destinationPath1.$file_name;
                            $file_path2 = $destinationPath2.$file_name;


                            copy($file_path,$file_path1);
                            copy($file_path,$file_path2);

                          
                            define('THUMBNAIL_IMAGE_MAX_WIDTH', 100);
                            define('THUMBNAIL_IMAGE_MAX_HEIGHT', 100);
                            $source_image_path=$file_path;
                            $thumbnail_image_path=$file_path1;
                            list($source_image_width, $source_image_height, $source_image_type) = getimagesize($source_image_path);
                            switch ($source_image_type) {
                                case IMAGETYPE_GIF:
                                    $source_gd_image = imagecreatefromgif($source_image_path);
                                    break;
                                case IMAGETYPE_JPEG:
                                    $source_gd_image = imagecreatefromjpeg($source_image_path);
                                    break;
                                case IMAGETYPE_PNG:
                                    $source_gd_image = imagecreatefrompng($source_image_path);
                                    break;
                            }
                            if ($source_gd_image === false) {
                                return false;
                            }
                            $source_aspect_ratio = $source_image_width / $source_image_height;
                            $thumbnail_aspect_ratio = THUMBNAIL_IMAGE_MAX_WIDTH / THUMBNAIL_IMAGE_MAX_HEIGHT;
                            if ($source_image_width <= THUMBNAIL_IMAGE_MAX_WIDTH && $source_image_height <= THUMBNAIL_IMAGE_MAX_HEIGHT) {
                                $thumbnail_image_width = $source_image_width;
                                $thumbnail_image_height = $source_image_height;
                            } elseif ($thumbnail_aspect_ratio > $source_aspect_ratio) {
                                $thumbnail_image_width = (int) (THUMBNAIL_IMAGE_MAX_HEIGHT * $source_aspect_ratio);
                                $thumbnail_image_height = THUMBNAIL_IMAGE_MAX_HEIGHT;
                            } else {
                                $thumbnail_image_width = THUMBNAIL_IMAGE_MAX_WIDTH;
                                $thumbnail_image_height = (int) (THUMBNAIL_IMAGE_MAX_WIDTH / $source_aspect_ratio);
                            }
                            $thumbnail_gd_image = imagecreatetruecolor($thumbnail_image_width, $thumbnail_image_height);
                            imagecopyresampled($thumbnail_gd_image, $source_gd_image, 0, 0, 0, 0, $thumbnail_image_width, $thumbnail_image_height, $source_image_width, $source_image_height);
                            imagejpeg($thumbnail_gd_image, $thumbnail_image_path, 90);
                            imagedestroy($source_gd_image);
                            imagedestroy($thumbnail_gd_image);
    
    
                            define('THUMBNAIL_IMAGE_MAX_WIDTH1', 275);
                            define('THUMBNAIL_IMAGE_MAX_HEIGHT1', 275);
                            $source_image_path=$file_path;
                            $thumbnail_image_path=$file_path2;
                            list($source_image_width, $source_image_height, $source_image_type) = getimagesize($source_image_path);
                            switch ($source_image_type) {
                                case IMAGETYPE_GIF:
                                    $source_gd_image = imagecreatefromgif($source_image_path);
                                    break;
                                case IMAGETYPE_JPEG:
                                    $source_gd_image = imagecreatefromjpeg($source_image_path);
                                    break;
                                case IMAGETYPE_PNG:
                                    $source_gd_image = imagecreatefrompng($source_image_path);
                                    break;
                            }
                            if ($source_gd_image === false) {
                                return false;
                            }
                            $source_aspect_ratio = $source_image_width / $source_image_height;
                            $thumbnail_aspect_ratio = THUMBNAIL_IMAGE_MAX_WIDTH1 / THUMBNAIL_IMAGE_MAX_HEIGHT1;
                            if ($source_image_width <= THUMBNAIL_IMAGE_MAX_WIDTH1 && $source_image_height <= THUMBNAIL_IMAGE_MAX_HEIGHT1) {
                                $thumbnail_image_width = $source_image_width;
                                $thumbnail_image_height = $source_image_height;
                            } elseif ($thumbnail_aspect_ratio > $source_aspect_ratio) {
                                $thumbnail_image_width = (int) (THUMBNAIL_IMAGE_MAX_HEIGHT1 * $source_aspect_ratio);
                                $thumbnail_image_height = THUMBNAIL_IMAGE_MAX_HEIGHT1;
                            } else {
                                $thumbnail_image_width = THUMBNAIL_IMAGE_MAX_WIDTH1;
                                $thumbnail_image_height = (int) (THUMBNAIL_IMAGE_MAX_WIDTH1 / $source_aspect_ratio);
                            }
                            $thumbnail_gd_image = imagecreatetruecolor($thumbnail_image_width, $thumbnail_image_height);
                            imagecopyresampled($thumbnail_gd_image, $source_gd_image, 0, 0, 0, 0, $thumbnail_image_width, $thumbnail_image_height, $source_image_width, $source_image_height);
                            imagejpeg($thumbnail_gd_image, $thumbnail_image_path, 90);
                            imagedestroy($source_gd_image);
                            imagedestroy($thumbnail_gd_image);
                                   
                            if($uploadSuccess)
                            {
                              $data['doctor_profile_picture_path'] = $file_name;
                            }  
                        }
                        
                        Doctors::insertGetId($data);
                        $dr_id = DB::getPdo()->lastInsertId();
                        
                        for($j=0; $j<count($spec_tags); $j++){
                            $speciality_name = Speciality::where('speciality_id', '=', $spec_tags[$j])->first();
                            Doctorsinterest::insert(array('doctor_id'=> $dr_id, 'doctor_speciality'=>$speciality_name['speciality_name'], 'doctor_speciality_details'=> Input::get('special_description_'.$spec_tags[$j]), 'speciality_id' => $spec_tags[$j]));
                        }
                        if($new_spec_tags != ''){
                            $speciality_name = Speciality::where('speciality_id', '=', $new_spec_tags)->first();
                            Doctorsinterest::insert(array('doctor_id'=> $dr_id, 'doctor_speciality'=>$speciality_name['speciality_name'], 'doctor_speciality_details'=> Input::get('special_description_new'), 'speciality_id' => $new_spec_tags));
                        }
                          if($data['doctor_gender']=="male"){
                            $gen="He";
                        }else{
                            $gen = "She";
                        }
                        $cat = DB::table('categories')->where('category_id','=',$data['category'])->first();
                        $admin_message = "Dr. ".$data['doctor_profile_name']." ".$data['doctor_qualification']." has been registered in Bookmyconsult. ".$gen." is specialized in ".$speciality_name['speciality_name']." (".$cat->category_name."). Please verify the doctor.";
                           $adminemails=Admin::where('admin_status','=',1)->get();
            foreach($adminemails as $admin){
                    if(Swift_Validate::email($admin['admin_email_id'])){
                        Mail::send('mailtemplates.adminappointment', array('admin_message' =>$admin_message), function($message) use ($admin){
                            $message->to($admin['admin_email_id'])->subject('New doctor registered');
                        });
                    }
                }                      
                        return Redirect::action('HomeController@anyShowSignupMsg',array());

            }
            else
            {
                    return Redirect::action('DoctorController@anyShowSignup',array())->withErrors($validator->messages())->withInput();
            }	
        }
        
        
//     ---show doctor login page        
        public function getLogin($flag='') 
        {
            $msg='';     
            return View::make('login.doctor_login')->with('error',$msg);
        } 
        

        
//      ---show dctor login page
        public function getLogout() 
        {
            Session::flush();
            Auth::logout();
            $msg='';
                    return Redirect::action('DoctorController@getLogin', array());
        }
        
//      ---check doctor authentication
        public function postCheck() 
        {          
            
                $name=Input::get('username');
                $pwd=md5(Input::get('password'));//echo $pwd;exit;
                $users = User::where('user_email_id' ,'=',$name)->where('user_password','=',$pwd)->where('user_type','=',0)->first();              
                if($users)
                {   
                    $approved_user = Doctors::where('user_id' ,'=',$users['user_id'])->first();              
                    if($approved_user['doctor_status']==0)
                    {
                        Session::flash('login_msg','Please Wait..You are not approved by admin');
                        return Redirect::action('DoctorController@getLogin', array());
                    }
                    else if($approved_user['doctor_status']==2)
                    {
                        Session::flash('login_msg','Sorry. You are blocked by admin.');
                        return Redirect::action('DoctorController@getLogin', array());
                    }
                    else 
                    {
                        $doctor=Doctors::where('user_id','=',$users['user_id'])->first();
                        Session::put('doctor_id', $doctor['doctor_id']);
                        Session::put('user_id', $users['user_id']);
                        Auth::login($users);
                        User::where('user_id','=',$users['user_id'])->update(array('last_login'=>date('Y-m-d H:i:s')));
                        return Redirect::action('DoctorController@anyShowFeed', array());
                    }
                }
                else
                {
                    Session::flash('login_msg','Sorry login failed..Retry again');
                    return Redirect::action('DoctorController@getLogin', array());
                }
            
        }
        
//      ---showing feed book
        public function anyShowFeed($msg='')
        {
            if($msg==1)
                $msg='Thanks For Agree';
            else if($msg==2)
                $msg='Thanks For Disagree';
            else if($msg==3)
                $msg='Thanks For Your Comments';
//            return View::make('common.doctor_home')->with('msg',$msg);
            Session::put('side_bar_active','Feed');
            return View::make('doctor_details.doctor_home')->with('msg',$msg);
        }
        
//      ---Agree the answer in Feed Section
        public function getAnswerAgree($answer_id)  
        {
            $data['agreed_answer_id']=$answer_id;
            $data['agreed_doctor_id']=Session::get('doctor_id');
            $data['answer_agreed_time']=date('Y-m-d H:i:s');
            
            Agreeddoctors::insert($data);
            DB::table('doctors_answers')->where('answer_id','=',$answer_id)->increment('no_of_agrees');
            
            $ans=Doctorsanswers::where('answer_id','=',$answer_id)->first();
            DB::table('doctors_score')->where('doctor_id','=',$ans['answered_doctor_id'])->increment('total_agrees_count');
            
            return Redirect::action('DoctorController@anyShowFeed', array(1));
                                    
        }
        
//      ---Disagree the answer in Feed Section
        public function getAnswerDisagree($answer_id)
        {
            $data['disagreed_answer_id']=$answer_id;
            $data['disagreed_doctor_id']=Session::get('doctor_id');
            $data['answer_disagreed_time']=date('Y-m-d H:i:s');
            
            Disagreeddoctors::insert($data);
            DB::table('doctors_answers')->where('answer_id','=',$answer_id)->increment('no_of_disagrees');
            
            $ans=Doctorsanswers::where('answer_id','=',$answer_id)->first();
            DB::table('doctors_score')->where('doctor_id','=',$ans['answered_doctor_id'])->increment('total_disagrees_count');
            
            return Redirect::action('DoctorController@anyShowFeed', array(2));
        }
        
//      ---save comments
        public function anySaveComments()
        {

            $data['doctor_comments']=Input::get('comments');
            $data['doctor_id']=Session::get('doctor_id');
            $data['answer_id']=Input::get('answer_idd');
            $data['comments_created_date']=date('Y-m-d H:i:s');
            DB::table('doctors_comments')->insertGetId($data);
            return Redirect::action('DoctorController@anyShowFeed', array(3));
        }

//      show doctor's question list
        public function anyShowQuestionList($flag='')   
        {
           $view_msg='';
           if($flag=='1') 
            {
                 $doctor=  Doctors::where('doctor_id','=',Session::get('doctor_id'))->first();
                 $view_msg="Thank You ".$doctor['doctor_profile_name']." For Your Answer !";
            }
            Session::put('side_bar_active','Question');
            return View::make('ask_now.doctor_question_list_new')->with(array('view_msg'=>$view_msg));
        }
        
//      ---datatable for listing doctors questions
        public function anyQuestionlist()       
        {     
                $docAns = Doctors::where('doctor_id','=',Session::get('doctor_id'))->first();
                //$qtn=Doctorsquestions::where('doctor_id','=',Session::get('doctor_id'))->orderBy('question_id','desc')->get();
                
                //$cnt=$qtn->count();
		$doc_int = Doctorsinterest::join('question_2_speciality','doctors_interest.speciality_id','=','question_2_speciality.speciality_id')->where('doctors_interest.doctor_id','=',Session::get('doctor_id'))->orderBy('question_2_speciality.question_id','desc')->get();
$cnt = count($doc_int);
                if($cnt>0)
                {
                   $qflag = 0; 
                   foreach($doc_int as $data)
                   {
                       $patient=Patientsquestiondetails::where('question_id','=',$data['question_id'])->first();
                       $ans_cnt=Doctorsanswers::where('question_id','=',$data['question_id'])->where('answered_doctor_id','!=',Session::get('doctor_id'))->count();
                       $total=Doctorsanswers::where('question_id','=',$data['question_id'])->where('answered_doctor_id','=',Session::get('doctor_id'))->first();
                       if(!$total['no_of_agrees'])
                           $agrees=0;
                       else 
                           $agrees=$total['no_of_agrees'];
                       if(!$total['no_of_disagrees'])
                           $disagrees=0;
                       else
                            $disagrees=$total['no_of_disagrees'];
                       if(!$total['no_of_thanked_patients'])
                           $thanks=0;
                       else
                           $thanks=$total['no_of_thanked_patients'];
                       
                       $pat=  Patient::where('patient_id','=',$patient['patient_id'])->first();
                       $row1=$pat['patient_profile_name'];
                       $row2=substr($patient['patient_question'],0,50)."...";
                       $time=date('d-m-Y H:i:s',strtotime($patient['question_ask_time']));
                       
                       if($total['no_of_agrees']=='')
                       {
                            if($docAns['verified'] == 0){
                                $row3="-";
                            }
                            else{ 
                                $row3= "<b>".link_to_action('DoctorController@anyShowAnswerPage', 'Give',array($data['question_id'],$patient['patient_id']),array('title'=>'Click Me For Answering'))."</b>";
                            }
                            $row1="<b>".$row1."</b>";
                            $row2="<b>".$row2."</b>";
                            $patient['question_ask_time']="<b>".$patient['question_ask_time']."</b>";
                            $agrees="<b>".$agrees."</b>";
                            $disagrees="<b>".$disagrees."</b>";
                            $thanks="<b>".$thanks."</b>";
                            $row3="<b>".$row3."</b>";
                            $ans_cnt="<b>".$ans_cnt." other doctor</b>";

                       }
                       else
                       {
                           $ans_cnt=$ans_cnt." other doctor"; 
                           $row3=link_to_action('DoctorController@anyShowDetailsPage', 'View',array($data['question_id'],$patient['patient_id']),array('title'=>'Click Me For Viewing Answer'));
                       }
                       
                        if($patient['approve_status'] == 1){
                            $qflag = 1;
                            $output['aaData'][] = array($row1,$row2,$patient['question_ask_time'],$agrees,$disagrees,$thanks,$row3); 
                        }
                    }
                    if($qflag == 0){
                        $output = array('aaData' => array());
                    }
                }
                else
                {
                    $output = array('aaData' => array());
                } 
                echo json_encode($output);                
        } 
        
//      ---show page for typing his answer
        public function anyShowAnswerPage($question_id='',$patient_id='',$error='') 
        { 
           if($error==1) 
               $error="Your answer can't be null";
            Session::put('side_bar_active','Question');
            return View::make('ask_now.doctor_answer_contributing_page_new')->with(array('question_id'=>$question_id,'patient_id'=>$patient_id,'error'=>$error));
        }
        
//      ---show page for viewing his answer
        public function anyShowDetailsPage($question_id)   
        {
            Session::put('side_bar_active','Question');
            return View::make('ask_now.doctor_answer_viewing_page_new')->with(array('question_id'=>$question_id));
        }
	 public function anyShowHelpPage($id)   
        {
            Session::put('side_bar_active','Help');
            return View::make('doctor_details.helplink')->with('menu_id',$id);
        }
        
//      ---submit answer for the patient's question
        public function anyAnswer() 
        {
                if(Input::get('answer')=='')
                {
                    Input::flash();
                    $question_id=Input::get('question_id');
                    $patient_id=Input::get('patient_id');
                    $error=1;
                    return Redirect::action('DoctorController@anyShowAnswerPage', array($question_id,$patient_id,$error));
                }
                $data['answer']=Input::get('answer');
                $data['answered_time']=date('Y-m-d H:i:s');
                $data['question_id']=Input::get('question_id');
                $data['answered_doctor_id']=Session::get('doctor_id');
                $data['short_answer']=ucfirst(Input::get('short_answer'));
                
                if (Input::hasFile('answer_image'))
                {
                    $file = Input::file('answer_image');
                    $file_org = $file->getClientOriginalName();
                    $file_name = Str::random(10).'_'.$file_org;
                    $destinationPath = 'images/upload/';
                    $uploadSuccess = Input::file('answer_image')->move($destinationPath, $file_name);
                    $file_path = $destinationPath.$file_name;
                    if($uploadSuccess)
                    {
                      $data['answer_image']= $file_name;
                    }  
                }
                
                $cnt=DB::table('doctors_answers')->where('question_id','=',$data['question_id'])->where('answered_doctor_id','=',Session::get('doctor_id'))->count();
                if($cnt==0)
                {
                     Doctorsanswers::insertGetId($data);    
                }

                DB::table('doctors_score')->where('doctor_id','=',$data['answered_doctor_id'])->increment('total_answering_count');

                DB::table('patients_question_details')->where('question_id','=',Input::get('question_id'))->update(array('answered'=> 1));

                $question_id=Input::get('question_id');            
                $existence=  Questiontagrelation::where('question_id','=',$question_id)->count();
                if($existence<1)
                {           
                    $problem_row=  Patientsquestiondetails::where('question_id','=',$question_id)->first();
                    $problem=  str_replace(' ',',',$problem_row['patient_question']);
                    Questiontagrelation::insert(array('question_id' => $question_id,'related_tag_names'=> $problem));
                }
                
                $adminemails=Admin::where('admin_status','=',1)->get();
                $patient_row=  Patientsquestiondetails::where('question_id','=',$question_id)->first();
                $doctor_row=  Doctors::where('doctor_id','=',$data['answered_doctor_id'])->first();
                //$patient_id=$patient_row['patient_id'];
                $patient_id=Input::get('patient_id');
                $user_row=  Patient::where('patient_id','=',$patient_id)->first();
                $user_id=$user_row['user_id'];
                $email_row=  User::where('user_id','=',$user_id)->first();
                $email_id=$email_row['user_email_id']; 
		//$email_id = 'anjana.nath@calpinetech.com';              
                $ans_drid=$data['answered_doctor_id'];
                $ans_drname = $doctor_row['doctor_profile_name'];
                $question = $patient_row['patient_question'];
                $answer = Doctorsanswers::where('answered_doctor_id','=',$ans_drid)->where('question_id','=',$question_id)->first();
                $ans ="<b style='font-size: 12px!important;color:#EF205A;'>Answer:</b><br/><p style='font-size:12px!important;font-style:italic;color:gray;'><b>".$answer['answer']."</b></p>";
                $link='<a href="'.URL::to('/').'/home/show-question-answer/'.$question_id.'" style="text-decoration:none;display:block;width:12%;min-height:auto;background:#EF205A;padding:5px;text-align:center;border-radius:5px;color:white;font-weight:bold">See all answers</a>';
		 $ans_admin ="";
$pat_msg = "<p style='color:gray;'>Great news! Dr. ".$doctor_row['doctor_profile_name'].", an expert in the Bookmyconsult network just answered your question ! </p>";
                $admin_msg = "<p style='color:gray;'> Dr. ".$doctor_row['doctor_profile_name'].", has been answered the patient ".$user_row['patient_fname']."'s question. </p>";
                $pat_subject = $user_row['patient_fname'].', Dr.'.$doctor_row['doctor_profile_name'].' has answered a question you asked!';

 if(Swift_Validate::email($email_id)){
                  
Mail::send('mailtemplates.doctoranswermail', array('patient_fname' =>$user_row['patient_fname'],'bodymsg' =>$pat_msg,'question'=> $question,'answer'=>$ans, 'link' =>$link), function($message) use ($email_id,$pat_subject){
                        $message->to($email_id,$pat_subject)->subject($pat_subject);
                    });

                }
                foreach($adminemails as $admin){
                    if(Swift_Validate::email($admin['admin_email_id'])){
                        
 Mail::send('mailtemplates.doctoranswermail', array('patient_fname' =>'Admin','bodymsg' =>$admin_msg,'question'=> $question,'answer'=>$ans_admin, 'link' =>$link), function($message) use ($admin){
                            $message->to($admin['admin_email_id'])->subject('A health question got an answer!');
                        });
                    }
                }
                
               
                $smsArr = array();
                $smsArr['patient_name'] = $user_row['patient_fname'].' '.$user_row['patient_lname'];
                $smsArr['doctor_name'] = $doctor_row['doctor_profile_name'];
                $smsArr['question_time'] = date('d/M/Y D',strtotime($patient_row['question_ask_time']));
                $smsArr['patient_contact_no'] = $user_row['patient_contact_no'];
                $smsArr['config_key'] = 'SMS_TEXT_ANS';
		$smsArr['link'] = URL::to('/').'/home/show-question-answer/'.$question_id;
                $sendsms = AppHelper::send_sms($smsArr);
		
                //$otherdrs = User::join('doctors','doctors.user_id','=','users.user_id')->where('doctors.doctor_id', '<>', $ans_drid)->get();
                $otherdrs = Doctors::join('users','users.user_id','=','doctors.user_id')->where('doctors.doctor_id', '<>', $ans_drid)->where('doctors.doctor_status','=',1)->where('doctors.verified','=',1)->where('doctors.doctor_unsubscribe','=',0)->get();
               
               foreach ($otherdrs as $drs){
                  
                    $othr_dr_link = '<a href="'.URL::to('/').'/home/show-answer-page/'.$drs['doctor_id'].'/'.$question_id.'" style="text-decoration:none;display:block;width:12%;min-height:auto;background:#EF205A;padding:5px;text-align:center;border-radius:5px;color:white;font-weight:bold">Suggest your answer</a>';
                    if(Swift_Validate::email($drs['user_email_id'])){
                        
                     Mail::send('mailtemplates.otherdocsuggest', array('other_drname' =>$drs['doctor_profile_name'], 'doctor_name' =>$ans_drname,'question'=> $question,'answer'=>$answer['answer'], 'link' =>$othr_dr_link), function($message) use ($drs){
                            $message->to($drs['user_email_id'])->subject('A health question got an answer! Suggest your answer!');
                        });
                       
                    }
                }
//$drs['user_email_id'] 
                return Redirect::action('DoctorController@anyShowQuestionList', array(1));

        }
        
//      ---show testimaonial list page  
        public function anyShowPatientsComments($flag='')
        {
            if($flag==1)
            {
                $view_msg='Testimonial is Rejected';
            }
            else if($flag==2) 
            {
                $view_msg='Testimonial is Approved';
            }
	   else if($flag==22){
                $view_msg='Testimonial is Removed';
            }
            else
            {
                $view_msg=$flag;
            }
            Session::put('side_bar_active','Testimonials');
            return View::make('feedback.doctor_comments_list_new')->with('msg',$view_msg);
        }

//      ---Datatable for listing testimonials      
        public function anyCommentlist()
        {            
            $msg= Usercomments::where('doctor_id','=',Session::get('doctor_id'))->orderby('comments_created_date','desc')->get();
            if($msg->count()>0)
            {
               foreach($msg as $data)
               {
                    $name=$data['user_name'];
                    $email=$data['user_email_id'];
                    $comments=substr($data['user_comments'],0,50)."...";
                    $time=date('d-m-Y H:i:s',strtotime($data['comments_created_date']));

                    if($data['approve_status']==0) 
                    {    
                        $icon1=asset("images/tick.jpeg");
                        $status1=html_entity_decode(link_to_action('DoctorController@getCommentstoggle', '<img src="'.$icon1.'"alt=asknow width=20 height=20/>',array($data['comment_id']),array('title'=>'Click Me For Approval')));
                        $icon2=asset("images/gray_wrong.jpeg");
                        $status2='<img src="'.$icon2.'"alt=asknow width=20 height=20/>';

                    }
                    else 
                    {
                        $icon1=asset("images/gray_tick.jpeg");
                        $status1='<img src="'.$icon1.'"alt=asknow width=20 height=20/>';
                        $icon2=asset("images/wrong.jpeg");
                        $status2=html_entity_decode(link_to_action('DoctorController@getCommentstoggle', '<img src="'.$icon2.'"alt=asknow width=20 height=20/>',array($data['comment_id']),array('title'=>'Click Me For Cancel')));
                    }

                    $name= link_to_action('DoctorController@anyShowCommentPage', $name,array($data['comment_id']),array('title'=>'Show Details'));
                    $email=link_to_action('DoctorController@anyShowCommentPage', $email,array($data['comment_id']),array('title'=>'Show Details'));
                    $comments=link_to_action('DoctorController@anyShowCommentPage', $comments,array($data['comment_id']),array('title'=>'Show Details'));
                    $time= link_to_action('DoctorController@anyShowCommentPage', $time,array($data['comment_id']),array('title'=>'Show Details'));
$row9=html_entity_decode(link_to_action('DoctorController@anyDrRemoveComments', '<img src="'.asset('images/del2.jpeg').'"alt=asknow width=20 height=20/>',array($data['comment_id']),array('title'=>'Delete Comment','onclick'=>"return confirm('Are you sure to delete this Comment?')")));
			if($data['viewed_status']==0){
                        $name = '<b>'.$name.'</b>';
                        $email = '<b>'.$email.'</b>';
                        $comments = '<b>'.$comments.'</b>';
                        $time = '<b>'.$time.'</b>';
                    }
                    $output['aaData'][] = array($name,$email,$comments,$time,$status1,$status2,$row9);     
               }
            }
            else
            {
                $output = array('aaData' => array());
            }
            echo json_encode($output);    
        }  

// ----- Remove comments from database
        
        public function anyDrRemoveComments($cid){
            if($cid!=''){
        DB::table('user_comments')->where('comment_id','=',$cid)->delete();  
        
         return Redirect::action('DoctorController@anyShowPatientsComments', array(22));
          } 
        }

//      --- Toggle testimonial   
        public function getCommentstoggle($id)
        {
            $data1= Usercomments::where('comment_id','=',$id)->first();
            $flag=$data1->approve_status;

            if($flag==1)
            {
                $status=0;
            }
            else 
            {
                $status=1;
            }
 	$view_status =1;
            DB::table('user_comments')->where('comment_id','=',$id)->update(array('approve_status' => $status,'viewed_status'=>$view_status));
            return Redirect::action('DoctorController@anyShowPatientsComments', array(($status+1)));
        }

//      ---Show page for single testimonials       
        public function anyShowCommentPage($comment_id){
            Session::put('side_bar_active','Testimonials');
            return View::make('feedback.doctor_comment_single_new')->with(array('comment_id' => $comment_id));
        }
        
//      ---show page for listing FEATURES List     
        public function anyShowFeaturesList($flag=''){
                $view_msg='';
                if($flag==1)
                    $view_msg='1 Feature Deleted';
                else if($flag==2)
                    $view_msg='1 Feature Added';
                else if($flag==3)
                    $view_msg='1 Feature Modified';
                
                Session::put('side_bar_active','Disease');
               return View::make('diseasefeature.doctor_disease_feature_list_new')->with(array('msg' =>$view_msg));
        }
        
//      ---Datatable for listing features      
        public function anyFeaturelist()
        {            
            $msg= Diseasefeatures::where('written_doctor_id','=',Session::get('doctor_id'))->orderby('feature_name','asc')->get();
            if($msg->count()>0)
            {
               foreach($msg as $data)
               {
                    $edit='';
                    $name=$data['feature_name'];
                    $type=$data['feature_type'];
                    if($type=='s')  
                        $type= "<img src=".asset('images/thermo2.jpeg')." alt=asknow width=20 height=20 title=Symptom />";
                    else 
                        $type="<img src=".asset('images/medication.jpeg')." alt=asknow width=20 height=20 title=Medication />";
                    $disease=substr($data['related_tag_name'],0,50);
                    $edit=html_entity_decode(link_to_action('DoctorController@anyShowAddEditFeature', '<img src="'.asset("images/edit.jpeg").'"alt=asknow width=20 height=20/>',array($data['feature_id']),array('title'=>'Edit Feature')));
                    $del= "<span style='cursor:pointer;'><img src=".asset('images/del2.jpeg')." alt=asknow title='Remove Feature' width=20 height=20 onclick=Confirm(".$data['feature_id'].") /></span>";
                    $output['aaData'][] = array($name,$type,$disease."...",$edit,$del);     
               }
            }
            else
            {
                $output = array('aaData' => array());
            }
            echo json_encode($output);    
        } 
        
//      ---show page for adding/editing feature info   
        public function anyShowAddEditFeature($id=0,$msg='')   
        {
            $data['id']=$id;
            $data['msg']=$msg;
            Session::put('side_bar_active','Disease');
            return View::make('diseasefeature.doctor_disease_feature_page_new',$data);
        }
        
//      Remove Selected features       
        public function anyRemovefeature($id=0)
        {
               DB::table('disease_features')->where('feature_id','=',$id)->delete();
               return Redirect::action('DoctorController@anyShowFeaturesList', array(1));
        }
                 
//      Add/edit features info        
        public function postAddEditFeature($id) 
        {     
//            $validator = Validator::make(array('feature_name' => Input::get('feature_name')), array('feature_name' => 'unique:disease_features'));
//            if ($validator->fails())
//            { 
                $data['feature_name']=Input::get('feature_name');
                $data['related_tag_name']=Input::get('related_tag_name');
                $data['feature_type']=Input::get('feature_type');
                $data['written_doctor_id']=Session::get('doctor_id');
                
                if($id==0)
                {

                        Diseasefeatures::insertGetId($data);
                        $view_msg=2;    
                }
                else
                {
                        DB::table('disease_features')->where('feature_id','=',$id)->update($data);
                        $view_msg=3;
                }
                return Redirect::action('DoctorController@anyShowFeaturesList', array($view_msg));
//            }
//            else
//            {
//                return Redirect::action('DoctorController@anyShowAddEditFeature', array($id,1));
//            }

        }
        
//      ---show page for listing VFEATURES List     
        public function anyShowVFeaturesList($flag='') 
        {
            $view_msg='';
            if($flag==1)
                $view_msg='Thanks for Vote Up';
            else if($flag==2)
                $view_msg='Thanks for Vote Down';
            Session::put('side_bar_active','Vote');
            return View::make('diseasefeature.doctor_disease_vfeature_list_new')->with(array('msg' =>$view_msg));
        }
        
 //     ---Datatable for listing vfeatures      
        public function anyVfeaturelist()
        {            
            $msg= Diseasefeatures::where('written_doctor_id','!=',Session::get('doctor_id'))->where('status','=',0)->orderBy('feature_name','asc')->get();
            if($msg->count()>0)
            {
               foreach($msg as $data)
               {
                    $edit='';
                    $name=$data['feature_name'];
                    $type=$data['feature_type'];
                    if($type=='s')  
                        $name= $name." [ s ] ";
                    else 
                        $name=$name." [ m ] ";
                    
                    $name= link_to_action('DoctorController@anyShowVfeature',$name,array($data['feature_id']));

                    $disease=substr($data['related_tag_name'],0,50);

                    $author_data=  Doctors::where('doctor_id','=',$data['written_doctor_id'])->first();
                    $author=$author_data['doctor_profile_name'];
                    

                    $up_cnt=$data['upvote_count'];
                    $status_cnt=Diseasefeaturesvote::where('doctor_id','=',Session::get('doctor_id'))->where('feature_id','=',$data['feature_id'])->where('up_down','=',1)->count();
                    if($status_cnt>0)
                    {
                        $up_icon="<img src=".asset('images/grayup.jpeg')." alt=asknow width=20 height=20 title='Already Vote Up' />";
                    }
                    else
                    {
                        $up_icon="<img src=".asset('images/up.jpeg')." alt=asknow width=20 height=20 title=Voteup />";
                        $up_icon=html_entity_decode(link_to_action('DoctorController@getUptoggle', $up_icon,array($data['feature_id'])));
                    }
                    $up=$up_cnt."".$up_icon;
                    
                    
                    $down_cnt=$data['downvote_count'];
                    $score=Doctorsscore::where('doctor_id','=',Session::get('doctor_id'))->first();
                    $my_score=$score['total_agrees_count']+$score['total_followers_count']+$score['total_thanks_count']+$score['total_answering_count']+$score['total_approved_blogs_count']-$score['total_disagrees_count'];
                    if($my_score>5)
                    {
                        $status_cnt=Diseasefeaturesvote::where('doctor_id','=',Session::get('doctor_id'))->where('feature_id','=',$data['feature_id'])->where('up_down','=',0)->count();
                        if($status_cnt>0)
                            $down_icon="<img src=".asset('images/graydown.jpeg')." alt=asknow width=20 height=20 title='Already Vote Down' />";
                        else
                        {
                            $down_icon="<img src=".asset('images/down.jpeg')." alt=asknow width=20 height=20 title=Votedown />";
                            $down_icon=html_entity_decode(link_to_action('DoctorController@getDowntoggle', $down_icon,array($data['feature_id'])));
                        }
                    }
                    else
                    {
                        $down_icon="<img src=".asset('images/graydown.jpeg')." alt=asknow width=20 height=20 title='Vote Down requires doctorscore=5' />";
                    }
                    $down=$down_cnt."".$down_icon;

                    
                    $output['aaData'][] = array($name,$disease."...",$author,$up,$down);     
               }
            }
            else
            {
                $output = array('aaData' => array());
            }
            echo json_encode($output);    
        } 
        
//      ---Voteup   
        public function getUptoggle($id)
        {

                DB::table('disease_features')->where('feature_id','=',$id)->increment('upvote_count'); 

                $data['feature_id']=$id;
                $data['doctor_id']=Session::get('doctor_id');
                $data['up_down']=1;
                Diseasefeaturesvote::insertGetId($data);

               $row = Diseasefeatures::where('feature_id' ,'=',$data['feature_id'])->first();              
               $status=$row['upvote_count']-$row['downvote_count'];
               if($status>=5)
                    DB::table('disease_features')->where('feature_id',$id)->update(array('status' => 1));
               else
                    DB::table('disease_features')->where('feature_id',$id)->update(array('status' => 0));
               return Redirect::action('DoctorController@anyShowVFeaturesList', array(1));
        }
    
//  ---Vote down
    public function getDowntoggle($id)
    {
            DB::table('disease_features')->where('feature_id','=',$id)->increment('downvote_count'); 
            
            $data['feature_id']=$id;
            $data['doctor_id']=Session::get('doctor_id');
            $data['up_down']=0;
            Diseasefeaturesvote::insertGetId($data);

           $row = Diseasefeatures::where('feature_id' ,'=',$data['feature_id'])->first();              
           $status=$row['upvote_count']-$row['downvote_count'];
           if($status>=5)
                DB::table('disease_features')->where('feature_id',$id)->update(array('status' => 1));
           else
                DB::table('disease_features')->where('feature_id',$id)->update(array('status' => 0));
           return Redirect::action('DoctorController@anyShowVFeaturesList', array(2));
    }

//      ---show single page for viewing  
        public function anyShowVfeature($id)
        {
            $data['msg']='';
            $data['id']=$id;
            Session::put('side_bar_active','Vote');
            return View::make('diseasefeature.doctor_disease_vfeature_page_new',$data);
        }
    
//      ---show page for listing holiday        
        public function getShowHolidayList($flag='') 
        {
            if($flag==1)
                $view_msg='1 Special Holiday Deleted';
            else if($flag==2)
                $view_msg='1 Special Holiday Added';
            else
                $view_msg='';
            Session::put('side_bar_active','Special');
            return View::make('clinic.doctor_holiday_list_new')->with(array('view_msg' => $view_msg));
        }

//      ---datatable for listing holiday      
        public function anyHolidaylist()
        {            
            $cnt=0;
            $clinic_data=Doctorsclinicdetails::where('doctor_id','=',Session::get('doctor_id'))->get();
            if($clinic_data->count()>0)
            {
                foreach($clinic_data as $clinic)
                {
                    $holidays= Clinicspecialholidays::where('clinic_id','=',$clinic['clinic_id'])->get();
                    if($holidays->count()>0)
                    {
                       foreach($holidays as $data)
                       {
                          $cli = preg_replace('/[^a-zA-Z0-9_ %\[\]\.\(\)%&-\']/', '', $clinic['clinic_name']);
                           //$edit_link=html_entity_decode(link_to_action('AdminController@anyShowAddHoliday', '<img src="'.asset("images/index.jpeg").'"alt=asknow width=40 height=40/>',array($data['holiday_id'])));                  
                           $del_link= "<span style='cursor:pointer;'><img src=".asset('images/del2.jpeg')." alt=delete width=20 height=20 onclick=Confirm(".$data['holiday_id'].") /></span>";
                           $output['aaData'][] = array($cli,date('d-M-Y',strtotime($data['holiday_from'])),date('d-M-Y',strtotime($data['holiday_to'])),$data['reason'],$del_link);  
                          $cnt++;
                       }
                    }
                }
            }
            else
            {
                  $output = array('aaData' => array());
                  $cnt++;
            }
            if($cnt==0)
                $output = array('aaData' => array());
            echo json_encode($output);    
        }    

 //     ---Remove special holidays
        public function anyRemoveholiday($id=0)
        {
               DB::table('clinic_special_holidays')->where('holiday_id','=',$id)->delete();
               return Redirect::action('DoctorController@getShowHolidayList', array(1));
        }
        
//      ---show page for add holiday        
        public function anyShowAddHoliday() 
        {
            Session::put('side_bar_active','Special');
            return View::make('clinic.doctor_add_holiday_new')->with(array('view_msg' => ' '));
        }
        
//      ---save special holidays        
        public function postSaveHoliday() 
        {
            $yesterday = date('Y-m-d', strtotime(Input::get('datepickerfrom')) - 86400);
            $rules = array(
                        'datepickerfrom' => 'required',
                        'datepickerto' => 'required|after:'.$yesterday
                    );
            
            $messages = array(
                        'datepickerfrom.required' => 'Holiday start date is required',
                        'datepickerto.required' => 'Holiday end date is required',
                        'datepickerto.after' => 'Holiday end date should be on or after '.Input::get('datepickerfrom'),
                    );
            
            $validator = Validator::make(Input::all(), $rules, $messages);
            if($validator->passes()){  
                $data['clinic_id']=Input::get('clinic');
                $data['holiday_from']=Input::get('datepickerfrom');
                $data['holiday_to']=Input::get('datepickerto');
                $data['reason']=Input::get('reason');
                if($data['reason']=='')
                {
                   $data['reason']='Personal'; 
                }
                //Clinicspecialholidays::insertGetID($data);
                //return Redirect::action('DoctorController@getShowHolidayList', array(2));
$check_holidays = Clinicspecialholidays::where('clinic_id','=',$data['clinic_id'])->where('holiday_from','=',$data['holiday_from'])->where('holiday_to','=',$data['holiday_to'])->first();
                if(count($check_holidays)>0){
                 Session::put('same_holiday','This clinic already has holidays on same dates');
                  return Redirect::action('DoctorController@anyShowAddHoliday', array())->withErrors($validator->messages())->withInput();
                }
                else{
                Clinicspecialholidays::insertGetID($data);
                return Redirect::action('DoctorController@getShowHolidayList', array(2));
                }
            }
            else{
                return Redirect::action('DoctorController@anyShowAddHoliday', array())->withErrors($validator->messages())->withInput();
            }
        }
        

//      ---show page for listing clinic        
        public function anyShowClinicList($flag='') 
        {
            $view_msg='';
            if($flag==1)
                $view_msg='1 Clinic Deleted';
            else if($flag==2)
                $view_msg='1 Clinic Added';
            else if($flag==3)
                $view_msg='1 Clinic Modified';
	    else if($flag==15)
                $view_msg='Appointments are pending in this clinic.';
            Session::put('side_bar_active','Clinic');
            return View::make('clinic.doctor_clinic_list_new')->with(array('view_msg' =>$view_msg));
        }
        
//      ---Datatable for listing clinic      
        public function anyCliniclist()
        {            
            $clinic=  Doctorsclinicdetails::where('doctor_id','=',Session::get('doctor_id'))->get();
            if(count($clinic))
            {
               foreach($clinic as $data)
               {
                   $row1=stripslashes($data['clinic_name']);

                   $city=  Location::where('location_id','=',$data['clinic_city_id'])->first();
                   
                   $city_name=$city['location_name'];
                   if($data['clinic_primary_status']==0) 
                   {    
                       $icon=asset("images/wrong.jpeg");
                   }
                   else 
                   {
                       $icon=asset("images/tick.jpeg");
                   }
                   $row2="<img src='".$icon."' alt=asknow width=20 height=20 id=mark /> ";

                   $row3=html_entity_decode(link_to_action('DoctorController@anyShowAddEditClinic', '<img src="'.asset("images/edit.jpeg").'"alt=asknow width=20 height=20/>',array($data['clinic_id'])));

                   if($data['clinic_primary_status']==0)
                   { 
                       $row4="<span style='cursor:pointer;'><img src=".asset('images/del2.jpeg')." alt=asknow width=20 height=20 onclick=Confirm(".$data['clinic_id'].") /></span>";
                   }
                   else 
                   {
                       $row4="<span style='cursor:pointer;'><img src=".asset('images/del2.jpeg')." alt=asknow width=20 height=20 onclick=Primaryconfirm(".$data['clinic_id'].") /></span>"; 
                   }                                                 
                  $output['aaData'][] = array($row1,$city_name,$row2,$row3,$row4);     
               }
            }
             else
            {
                $output = array('aaData' => array());
            }
            echo json_encode($output);    
        }        
        
//      ---show page for adding/editing clinic info   
        public function anyShowAddEditClinic($id='',$msg='',$messages='')   
        {
            $data['id']=$id;
            $data['msg']=$msg;
            $data['messages']=$messages;
            Session::put('side_bar_active','Clinic');
            return View::make('clinic.doctor_clinic_single_new',$data);
        }
          
//      ---Add/edit clinic info        
        public function postAddEditClinic($id) 
        {     
Session::put('side_bar_active','Clinic');
            Input::flash();
//            $validator = Validator::make(array('clinic_zipcode' => Input::get('clinic_zipcode'),'total_no_of_visitors' => Input::get('total_no_of_visitors'),'clinic_contact_no' => Input::get('clinic_contact_no'),'clinic_contact_no2' => Input::get('clinic_contact_no2')), array('clinic_zipcode' => 'numeric','total_no_of_visitors' => 'numeric','clinic_contact_no' => 'numeric','clinic_contact_no2' => 'numeric'));
//            if ($validator->fails())
//            {                
//                $data['id']=$id;
//                $data['msg']='';
//                $error_message='';
//                $messages=$validator->messages();
//                foreach($messages->all() as $message)
//                {
//                    $error_message.="<br>".$message;
//                }
//                $data['messages']=$error_message;
//                
//                return Redirect::action('DoctorController@anyShowAddEditClinic', array($id,$msg,$error_message));
//                //return View::make('clinic.doctor_clinic_single',$data);
//            }
//            else
//            {
//                if((Input::get('clinic_contact_no')=='')&&(Input::get('clinic_contact_no2')!=''))
//                {
//                    $data['id']=$id;
//                    $data['msg']='';
//                    $error_message="<br>First fill clinic contact no 1";
//                    $data['messages']=$error_message;
//                    return View::make('clinic.doctor_clinic_single',$data);
//                }

$rules = array(
                                
                                'clinic_contact_no' => 'numeric|digits_between:10,10',
                'clinic_contact_no2' => 'numeric|digits_between:10,10'
                            );

 $messages = array(
                'clinic_contact_no.digits_between' => 'Please enter valid phone number',
                'clinic_contact_no2.digits_between' => 'Please enter valid phone number'
            );

 $validator = Validator::make(Input::all(), $rules, $messages);
if ($validator->passes()) 
           {
                $holidays='';
 $timeslot =array();
                if(Input::get('clinic_holidays'))
                {
                    $holidays=implode(',',Input::get('clinic_holidays'));
                }
                $data['clinic_name']=Input::get('clinic_name');
                $data['clinic_city_id']=Input::get('clinic_location');
                $data['clinic_state']=Input::get('clinic_state');
                $data['clinic_zipcode']=Input::get('clinic_zipcode');
                $data['clinic_contact_no']=Input::get('clinic_contact_no').",".Input::get('clinic_contact_no2');
                $data['clinic_consulting_time']=Input::get('clinic_consulting_time');
                $data['total_no_of_visitors']=Input::get('total_no_of_visitors');                
                $data['clinic_primary_status']=Input::get('clinic_primary_status');
                $data['clinic_holidays']=$holidays;
		$data['clinic_app_facility'] = Input::get('clinic_app_facility');
		if(count(Input::get('slot'))== 2){
                    
                    foreach(Input::get('slot') as $item){
                        $timeslot[]=$item;
                        
                    }
                 
                   $data['clinic_slots']=$timeslot[0].','.$timeslot[1];
                }
                else{
                    foreach(Input::get('slot') as $item){
                        $data['clinic_slots']=$item;
                        
                    }
                
                }
                $data['doctor_id']=Session::get('doctor_id');
                if($id==0)
                {
                    $cnt=DB::table('doctors_clinic_details')->where('doctor_id','=',$data['doctor_id'])->where('clinic_name','=',$data['clinic_name'])->count();
//                    if($cnt<=0)
//                    {
                        if($data['clinic_primary_status']=='1')
                        {
                            $result=DB::table('doctors_clinic_details')->where('doctor_id','=',$data['doctor_id'])->where('clinic_primary_status','=',1)->first(); 
                            if($result!=array())
                            {
                                DB::table('doctors_clinic_details')->where('doctor_id','=',$data['doctor_id'])->where('clinic_id','=',$result->clinic_id)->update(array('clinic_primary_status' =>0));
                            }
                        }
                        DB::table('doctors_clinic_details')->insertGetId($data);
                        $view_msg=2;    
//                    }
//                    else
//                    {
//                        $data['msg']='Clinic name "'.$data['clinic_name'].'" already exist';
//                        Input::flash();
//                        $data['id']=$id;
//                        $data['messages']='';
//                        return View::make('clinic.doctor_clinic_single',$data);     
//                    }
                }
                else
                {
                    if($data['clinic_primary_status']=='1')
                     {
                            $result=DB::table('doctors_clinic_details')->where('doctor_id','=',$data['doctor_id'])->where('clinic_primary_status','=',1)->first(); 
                            if($result!=array())
                            {
                                DB::table('doctors_clinic_details')->where('doctor_id','=',$data['doctor_id'])->where('clinic_id','=',$result->clinic_id)->update(array('clinic_primary_status' =>0));
                            }
                     }
                     DB::table('doctors_clinic_details')->where('clinic_id','=',$id)->update($data);
                     $view_msg=3;
                }
                return Redirect::action('DoctorController@anyShowClinicList', array($view_msg));

           }

else
            {
                    //return Redirect::action('DoctorController@anyShowAddEditClinic',array())->withErrors($validator->messages())->withInput();
               $data['id']=$id;
                $data['msg']='';
                    $data['messages'] = '';
                    $data['errors']=$validator->messages();
                    return View::make('clinic.doctor_clinic_single_new',$data);
            }
        } 
        
//      ---Remove clinic info
        public function anyRemoveclinic($id=0)
        {
               //DB::table('doctors_clinic_details')->where('clinic_id','=',$id)->delete();
              // return Redirect::action('DoctorController@anyShowClinicList', array(1));
		 $dr = Doctorsclinicdetails::where('clinic_id','=',$id)->first();
            $drid= $dr['doctor_id'];
            $check_clinic = Appointmentdetails::where('clinic_id','=',$id)->where('doctor_id','=',$drid)->where('appointment_time','>=',date('Y-m-d'))->get();
            if(count($check_clinic)>0){
              return Redirect::action('DoctorController@anyShowClinicList', array(15));  
            }
            else{
               DB::table('doctors_clinic_details')->where('clinic_id','=',$id)->delete();
               return Redirect::action('DoctorController@anyShowClinicList', array(1));
            }
        }
        
//      ---show page for describing speciality        
        public function anyShowSpeciality($flag='')
        {
            $view_msg='';
            if($flag==1)
                $view_msg='Speciality Updated';
            Session::put('side_bar_active','Tags');
            return View::make('speciality.doctor_speciality_new')->with('msg',$view_msg);;
        }
        
//      ---update speciality
        public function anyEditSpeciality()
        {
            DB::table('doctors_interest')->where('doctor_id','=',Session::get('doctor_id'))->update(array('doctor_speciality_details'=> Input::get('doctor_speciality_details')));
            $tags=Input::get('doctor_tags');
            DB::table('doctors')->where('doctor_id','=',Session::get('doctor_id'))->update(array('interested_tag_names' => $tags));
            return Redirect::action('DoctorController@anyShowSpeciality', array(1));
        }
        
//      ---show page for editing doctors profile
        public function anyShowEditProfile($flag='')
        {
                $data['msg']='';
                if($flag==1)
                    $data['msg']='Profile Edited Successfully';
                Session::put('side_bar_active','Profile');
                return View::make('doctor_details.doctor_profile_editing_new',$data); 
                
        }
       
//      ---edit doctors profile
        public function anyEditDoctor()  
        {    
	$verified_flag = "false";       
	    Input::flash();
            $rules = array(
                'profile_name' => 'required',
                'qualification' => 'required',
                'contact_no' => 'numeric',
                'city' => 'required',
                'category' => 'required'
        	 );
           $validator = Validator::make(Input::all(), $rules);
           if ($validator->passes()) 
           {                                         
                        $data['doctor_profile_name']=Input::get('profile_name');
                        $data['doctor_fname']=Input::get('first_name');
                        $data['doctor_mname']=Input::get('middle_name');
                        $data['doctor_lname']=Input::get('last_name');
                        $data['about_me']=Input::get('about_me');
                        $data['doctor_gender']=Input::get('gender');
                        $data['doctor_dob']=Input::get('dob');
                        $data['doctor_qualification']=Input::get('qualification');
                        $data['doctor_street_address']=Input::get('street_address');
                        $data['doctor_city_id']=Input::get('city');
                        $data['category']=Input::get('category');
                        $data['doctor_state']=Input::get('state');
                        $data['doctor_zipcode']=Input::get('zip_code');
                        $data['doctor_contact_no']=Input::get('contact_no');
                        $data['doctor_ph_status']=Input::get('phstatus');
                        $data['doctor_secondary_contact']=Input::get('sec_contact_no');
                        $data['doctor_ph_status_sec']=Input::get('phstatus_sec');
                        $data['doctor_url']=Input::get('url');
                        $data['fb_link']=Input::get('fb_link');
                        $data['linkedin_link']=Input::get('linkedin_link');
                        $data['google_plus_link']=Input::get('google_plus_link');
                        $data['twitter_link']=Input::get('twitter_link');
			if(Input::get('doctor_verified')==""){
                        $data['verified']='0';
                        }
                        else{
                            $data['verified']=Input::get('doctor_verified'); 
                        }
			if(Input::get('doctor_unsubscribe')==""){
                            $data['doctor_unsubscribe']=0;
                        }
                        else{
                            $data['doctor_unsubscribe']=1;
                        }
			$correct = DB::table('doctors')->where('doctor_id','=',Session::get('doctor_id'))->first();
                        if(($correct->verified)==0){
                            if(Input::get('doctor_verified')==1){
                                $verified_flag ="true";
                                $admin_message = "Dr. ".$correct->doctor_profile_name." has been verified his profile details. Please check for confirmation .";
                            }
                        }

                        if (Input::hasFile('doctor_profile_picture_path'))
                        {    
				                    
                          $file = Input::file('doctor_profile_picture_path');
                          $file_org = $file->getClientOriginalName();
                          $file_name = Str::random(10).'_'.$file_org;
                          $destinationPath = 'images/upload/';
                          $destinationPath1 = 'images/resize_small/';
                          $destinationPath2 = 'images/resize_large/';

                          $uploadSuccess = Input::file('doctor_profile_picture_path')->move($destinationPath, $file_name);
                          $file_path = $destinationPath.$file_name;
                          $file_path1 = $destinationPath1.$file_name;
                          $file_path2 = $destinationPath2.$file_name;

                          copy($file_path,$file_path1);
                          copy($file_path,$file_path2);

                            define('THUMBNAIL_IMAGE_MAX_WIDTH', 100);
                            define('THUMBNAIL_IMAGE_MAX_HEIGHT', 100);
                        
                            $source_image_path=$file_path;
                            $thumbnail_image_path=$file_path1;
                            list($source_image_width, $source_image_height, $source_image_type) = getimagesize($source_image_path);
                            switch ($source_image_type) {
                                case IMAGETYPE_GIF:
                                    $source_gd_image = imagecreatefromgif($source_image_path);
                                    break;
                                case IMAGETYPE_JPEG:
                                    $source_gd_image = imagecreatefromjpeg($source_image_path);
                                    break;
                                case IMAGETYPE_PNG:
                                    $source_gd_image = imagecreatefrompng($source_image_path);
                                    break;
                            }
                            if ($source_gd_image === false) {
                                return false;
                            }
                            $source_aspect_ratio = $source_image_width / $source_image_height;
                            $thumbnail_aspect_ratio = THUMBNAIL_IMAGE_MAX_WIDTH / THUMBNAIL_IMAGE_MAX_HEIGHT;
                            if ($source_image_width <= THUMBNAIL_IMAGE_MAX_WIDTH && $source_image_height <= THUMBNAIL_IMAGE_MAX_HEIGHT) {
                                $thumbnail_image_width = $source_image_width;
                                $thumbnail_image_height = $source_image_height;
                            } elseif ($thumbnail_aspect_ratio > $source_aspect_ratio) {
                                $thumbnail_image_width = (int) (THUMBNAIL_IMAGE_MAX_HEIGHT * $source_aspect_ratio);
                                $thumbnail_image_height = THUMBNAIL_IMAGE_MAX_HEIGHT;
                            } else {
                                $thumbnail_image_width = THUMBNAIL_IMAGE_MAX_WIDTH;
                                $thumbnail_image_height = (int) (THUMBNAIL_IMAGE_MAX_WIDTH / $source_aspect_ratio);
                            }
                            $thumbnail_gd_image = imagecreatetruecolor($thumbnail_image_width, $thumbnail_image_height);
                            imagecopyresampled($thumbnail_gd_image, $source_gd_image, 0, 0, 0, 0, $thumbnail_image_width, $thumbnail_image_height, $source_image_width, $source_image_height);
                            imagejpeg($thumbnail_gd_image, $thumbnail_image_path, 90);
                            imagedestroy($source_gd_image);
                            imagedestroy($thumbnail_gd_image);

                            define('THUMBNAIL_IMAGE_MAX_WIDTH1', 275);
                            define('THUMBNAIL_IMAGE_MAX_HEIGHT1', 275);

                        
                            $source_image_path=$file_path;
                            $thumbnail_image_path=$file_path2;
                            list($source_image_width, $source_image_height, $source_image_type) = getimagesize($source_image_path);
                            switch ($source_image_type) {
                                case IMAGETYPE_GIF:
                                    $source_gd_image = imagecreatefromgif($source_image_path);
                                    break;
                                case IMAGETYPE_JPEG:
                                    $source_gd_image = imagecreatefromjpeg($source_image_path);
                                    break;
                                case IMAGETYPE_PNG:
                                    $source_gd_image = imagecreatefrompng($source_image_path);
                                    break;
                            }
                            if ($source_gd_image === false) {
                                return false;
                            }
                            $source_aspect_ratio = $source_image_width / $source_image_height;
                            $thumbnail_aspect_ratio = THUMBNAIL_IMAGE_MAX_WIDTH1 / THUMBNAIL_IMAGE_MAX_HEIGHT1;
                            if ($source_image_width <= THUMBNAIL_IMAGE_MAX_WIDTH1 && $source_image_height <= THUMBNAIL_IMAGE_MAX_HEIGHT1) {
                                $thumbnail_image_width = $source_image_width;
                                $thumbnail_image_height = $source_image_height;
                            } elseif ($thumbnail_aspect_ratio > $source_aspect_ratio) {
                                $thumbnail_image_width = (int) (THUMBNAIL_IMAGE_MAX_HEIGHT1 * $source_aspect_ratio);
                                $thumbnail_image_height = THUMBNAIL_IMAGE_MAX_HEIGHT1;
                            } else {
                                $thumbnail_image_width = THUMBNAIL_IMAGE_MAX_WIDTH1;
                                $thumbnail_image_height = (int) (THUMBNAIL_IMAGE_MAX_WIDTH1 / $source_aspect_ratio);
                            }
                            $thumbnail_gd_image = imagecreatetruecolor($thumbnail_image_width, $thumbnail_image_height);
                            imagecopyresampled($thumbnail_gd_image, $source_gd_image, 0, 0, 0, 0, $thumbnail_image_width, $thumbnail_image_height, $source_image_width, $source_image_height);
                            imagejpeg($thumbnail_gd_image, $thumbnail_image_path, 90);
                            imagedestroy($source_gd_image);
                            imagedestroy($thumbnail_gd_image);
                          
                          if($uploadSuccess)
                          {
                            $data['doctor_profile_picture_path'] = $file_name;
                          }  
                        }
//                        if (Input::hasFile('scrolling_image1'))
//                        {                        
//                          $file = Input::file('scrolling_image1');
//                          $file_org = $file->getClientOriginalName();
//                          $file_name = Str::random(10).'_'.$file_org;
//                          $destinationPath = 'images/upload/';
//                          $uploadSuccess = Input::file('scrolling_image1')->move($destinationPath, $file_name);
//                          $file_path = $destinationPath.$file_name;
//                          if($uploadSuccess)
//                          {
//                            $data['scrolling_image1'] = $file_name;
//                          }  
//                        }
//                        if (Input::hasFile('scrolling_image2'))
//                        {                        
//                          $file = Input::file('scrolling_image2');
//                          $file_org = $file->getClientOriginalName();
//                          $file_name = Str::random(10).'_'.$file_org;
//                          $destinationPath = 'images/upload/';
//                          $uploadSuccess = Input::file('scrolling_image2')->move($destinationPath, $file_name);
//                          $file_path = $destinationPath.$file_name;
//                          if($uploadSuccess)
//                          {
//                            $data['scrolling_image2'] = $file_name;
//                          }  
//                        }

			if($verified_flag == "true"){
                            $adminemail = Admin::where('admin_status','=',1)->get();
                            foreach($adminemail as $admin){
                            if(Swift_Validate::email($admin['admin_email_id'])){
                        
                                 Mail::send('mailtemplates.adminappointment', array('admin_message'=>$admin_message), function($message) use ($admin){
                                 $message->to($admin['admin_email_id'])->subject('Doctor Verified');
                                });
                                
                                }
                            }
                        }
                        DB::table('doctors')->where('doctor_id','=',Session::get('doctor_id'))->update($data);  
                        return Redirect::action('DoctorController@anyShowEditProfile', array(1));
            }
            else
            {
                    return Redirect::action('DoctorController@anyShowEditProfile', array())->withErrors($validator->messages())->withInput();
            }	
        }
        
//      ---show doctor score graph
        public function anyShowScoreSheet() 
        {
            Session::put('side_bar_active','Score');
            return View::make('score.doctor_score_graph_new');    
        }
       
//      ---graphically represent the doctors score
        public function anyDoctor()
        {
                $r=Doctorsscore::where('doctor_id','=',Session::get('doctor_id'))->first();
                $table = array();
                $table['cols'] = array(
                array('label' => 'Doctor', 'type' => 'string'),
                array('label' => 'Score', 'type' => 'number')
                );
                $rows = array();

                $temp = array();
                $temp[] = array('v' => 'Agrees');
                $temp[] = array('v' => (int)$r['total_agrees_count']);
                $rows[] = array('c' => $temp);
                
                $temp = array();
                $temp[] = array('v' => 'Followers');
                $temp[] = array('v' => (int)$r['total_followers_count']);
                $rows[] = array('c' => $temp);


                $temp = array();
                $temp[] = array('v' => 'Thanks');
                $temp[] = array('v' => (int)$r['total_thanks_count']);
                $rows[] = array('c' => $temp);

                $temp = array();
                $temp[] = array('v' => 'Blogs');
                $temp[] = array('v' => (int)$r['total_approved_blogs_count']);
                $rows[] = array('c' => $temp);
                
                $temp = array();
                $temp[] = array('v' => 'Answers');
                $temp[] = array('v' => (int)$r['total_answering_count']);
                $rows[] = array('c' => $temp);

                $table['rows'] = $rows;
                $jsonTable = json_encode($table);
                echo $jsonTable; 
         } 
         
 //         ---Show page for listing appointments of the logged in doctor       
            public function anyShowAppointmentList($flag='')
            {
                    $data['view_msg']='';
                    if($flag==1)
                                    $data['view_msg']='Appointment is Rejected';      
                    else if($flag==2)
                                    $data['view_msg']='Appointment is Approved'; 
                    else if($flag==11)
                                    $data['view_msg']='Selected Appointments are Approved !';
                    else if($flag==22)
                                    $data['view_msg']="Please select an appointment to Approve.";
                     else if($flag==5)
                        $data['view_msg']="Appointment is Rejected";
                    Session::put('side_bar_active','Appointments');
                    return View::make('appointment.doctor_appointment_list_new',$data);
            }
    
//          ----Datatable for listing appointments
            public function anyAppointmentlist()
            {            
                $appointment=  Appointmentdetails::where('doctor_id','=',Session::get('doctor_id'))->where('appointment_time','>=',date('Y-m-d'))->get();
                if($appointment->count()>0)
                {
                   foreach($appointment as $data)
                   {
                        $pid=$data['patient_id'];
                        $pa=DB::table('patients')->where('patient_id','=',$pid)->first();
                        $name=$pa->patient_fname." ";
                        $name.=$pa->patient_lname;
                        $ho=DB::table('doctors_clinic_details')->where('clinic_id','=',$data['clinic_id'])->first();
                        $clinic_name=stripslashes($ho->clinic_name);
                        $contact_no=$pa->patient_contact_no;
                        $gender=$pa->patient_gender;
                        $appointment_time=date('d M Y',strtotime($data['appointment_time']));
                        $booking_time=date('d M Y H:i:s',strtotime($data['request_send_time']));
			$slotno =$data['slot_no'].' - '.$data['time_slot'];
			$dob =date('Y-m-d',strtotime($pa->patient_dob));
                        $dobObject = new DateTime($dob);
                        $nowObject = new DateTime();
                        $diff = $dobObject->diff($nowObject);
                        $age= $diff->y;
                        if($data['approve_status']==0) 
                        {    
			
                            $icon1=asset("images/tick.jpeg");
                            $status1=html_entity_decode(link_to_action('DoctorController@getAtoggle', '<img src="'.$icon1.'"alt=asknow width=20 height=20/>',array($data['appointment_id'],1),array('title'=>'Click Me For Approval')));
                            $icon2=asset("images/wrong.jpeg");
                            $status2=html_entity_decode(link_to_action('DoctorController@getAtoggle', '<img src="'.$icon2.'"alt=asknow width=20 height=20/>',array($data['appointment_id'],2),array('title'=>'Click Me For Reject')));
			$name="<b>".$name."</b>";
                        $age ="<b>".$age."</b>";
                        $gender = "<b>".$gender."</b>";
                        $contact_no = "<b>".$contact_no."</b>";
                        $appointment_time = "<b>".$appointment_time."</b>";
                        $slotno = "<b>".$slotno."</b>";
                        $clinic_name = "<b>".$clinic_name."</b>";
                        $booking_time = "<b>".$booking_time."</b>";
			
                        }
                        else 
                        {
                            $icon1=asset("images/gray_tick.jpeg");
                            $status1='<img src="'.$icon1.'"alt=asknow width=20 height=20/>';
                            $icon2=asset("images/wrong.jpeg");
                            $status2=html_entity_decode(link_to_action('DoctorController@getAtoggle', '<img src="'.$icon2.'"alt=asknow width=20 height=20/>',array($data['appointment_id'],2),array('title'=>'Click Me For Reject')));

                        }
                        
                        
                        $row1= Form::checkbox('check[]',$data['appointment_id'],false,array('class' => 'case','id' => 'case'));     

                        $output['aaData'][] = array($row1,$name,$age,$gender,$contact_no,$appointment_time,$slotno,$clinic_name,$booking_time,$status1,$status2);     
                   }
                }
                else
                {
                    $output = array('aaData' => array());
                }
                echo json_encode($output);    
            }
    
//  ---Toggle approval status of appointments    
   /* public function getAtoggle($id,$state)
    {   
        $time_slot = '';
        $data1= Appointmentdetails::where('appointment_id','=',$id)->first();
//        $flag=$data1->approve_status;
        
        $user=Patient::where('patient_id','=',$data1->patient_id)->first();
        $clinic=Doctorsclinicdetails::where('clinic_id','=',$data1->clinic_id)->first();
        $doctor=Doctors::where('doctor_id', '=', $clinic->doctor_id)->first();
        $patient=User::where('user_id','=',$user->user_id)->first();
        if($data1->time_slot == 'M')
            $time_slot = 'Morning';
        else if($data1->time_slot == 'E')
            $time_slot = 'Evening';
        
        if($state==1)
        {
            $status=1;
            $user_message="<br>Your appointment for the clinic <b> ".stripslashes($clinic->clinic_name)." </b> to consult <b>Dr.".$doctor->doctor_profile_name."</b> is scheduled for <b> ".date('d/M/Y l',strtotime($data1->appointment_time))."</b>, <b>".$time_slot."</b> & your appointment number is <b>".$data1->slot_no."</b>. <br>For confirm your appointment please call: ".$clinic->clinic_name." , ".$clinic->clinic_contact_no; 
            DB::table('appointment_details')->where('appointment_id',$id)->update(array('approve_status' => $status));
 if(Swift_Validate::email($patient->user_email_id)){
            Mail::send('mailtemplates.appointmenttemplate', array('first_name'=> $user->patient_fname,'last_name'=>$user->patient_lname,'user_message'=>$user_message), function($message) use ($patient){
                $message->to($patient->user_email_id)->subject('Appointment Info');
            });
            
            $smsArr = array();
            $smsArr['patient_name'] = $user->patient_fname.' '.$user->patient_lname;
            $smsArr['clinic_name'] = stripslashes($clinic->clinic_name);
            $smsArr['doctor_name'] = $doctor->doctor_profile_name;
            $smsArr['appointment_time'] = $data1->appointment_time;
            $smsArr['time_slot'] = $time_slot;
            $smsArr['slot_no'] = $data1->slot_no;
            $smsArr['patient_contact_no'] = $user->patient_contact_no;
            $smsArr['clinic_no'] = stripslashes($clinic->clinic_contact_no);
            if($state==1){
                $smsArr['config_key'] = 'SMS_TEXT_APP';
            }
            else if($state==2){
                $smsArr['config_key'] = 'SMS_TEXT_APP_REJECT';
            }
            
            $sendsms = AppHelper::send_sms($smsArr);
        }
        
        $flag=$status+1;
        return Redirect::action('DoctorController@anyShowAppointmentList', array($flag));
        }
        else if($state==2)
        {
            $status=0;
            $user_message="<br>Sorry. Your appointment for the clinic <b>".stripslashes($clinic->clinic_name)."</b> to consult <b>Dr.".$doctor->doctor_profile_name."</b> on <b>".date('d/M/Y l',strtotime($data1->appointment_time))." (".$time_slot.")</b> is cancelled. Please try another date.";
            DB::table('appointment_details')->where('appointment_id','=',$id)->delete();

 if(Swift_Validate::email($patient->user_email_id)){
            Mail::send('mailtemplates.appointmenttemplate', array('first_name'=> $user->patient_fname,'last_name'=>$user->patient_lname,'user_message'=>$user_message), function($message) use ($patient){
                $message->to($patient->user_email_id)->subject('Appointment Info');
            });
            
            $smsArr = array();
            $smsArr['patient_name'] = $user->patient_fname.' '.$user->patient_lname;
            $smsArr['clinic_name'] = stripslashes($clinic->clinic_name);
            $smsArr['doctor_name'] = $doctor->doctor_profile_name;
            $smsArr['appointment_time'] = $data1->appointment_time;
            $smsArr['time_slot'] = $time_slot;
            $smsArr['slot_no'] = $data1->slot_no;
            $smsArr['patient_contact_no'] = $user->patient_contact_no;
            $smsArr['clinic_no'] = stripslashes($clinic->clinic_contact_no);
            if($state==1){
                $smsArr['config_key'] = 'SMS_TEXT_APP';
            }
            else if($state==2){
                $smsArr['config_key'] = 'SMS_TEXT_APP_REJECT';
            }
            
            $sendsms = AppHelper::send_sms($smsArr);
        }
        
        $flag=$status+1;
        return Redirect::action('DoctorController@anyShowAppointmentList', array($flag));
        }
        
       

    }*/
    
public function getAtoggle($id,$state)
    {   
        $check_res = Appointmentdetails::where('appointment_id','=',$id)->first();
             if(count($check_res)>0){
        $time_slot = '';
        $data1= Appointmentdetails::where('appointment_id','=',$id)->first();
//        $flag=$data1->approve_status;
        
        $user=Patient::where('patient_id','=',$data1->patient_id)->first();
        $clinic=Doctorsclinicdetails::where('clinic_id','=',$data1->clinic_id)->first();
        $doctor=Doctors::where('doctor_id', '=', $clinic->doctor_id)->first();
        $patient=User::where('user_id','=',$user->user_id)->first();
        if($data1->time_slot == 'M')
            $time_slot = 'Morning';
        else if($data1->time_slot == 'E')
            $time_slot = 'Evening';
        
        if($state==1)
        {
            $status=1;
            $user_message="<br>Your appointment for the clinic <b> ".stripslashes($clinic->clinic_name)." </b> to consult <b>Dr.".$doctor->doctor_profile_name."</b> is scheduled for <b> ".date('d/M/Y l',strtotime($data1->appointment_time))."</b>, <b>".$time_slot."</b> & your appointment number is <b>".$data1->slot_no."</b>.For confirm your appointment please call: ".$clinic->clinic_name." , ".$clinic->clinic_contact_no; 
            
             if(Swift_Validate::email($patient->user_email_id)){
            Mail::send('mailtemplates.appointmenttemplate', array('first_name'=> $user->patient_fname,'last_name'=>$user->patient_lname,'user_message'=>$user_message), function($message) use ($patient){
                $message->to($patient->user_email_id)->subject('Appointment Info');
            });
            
            $smsArr = array();
            $smsArr['patient_name'] = $user->patient_fname.' '.$user->patient_lname;
            $smsArr['clinic_name'] = stripslashes($clinic->clinic_name);
            $smsArr['doctor_name'] = $doctor->doctor_profile_name;
            $smsArr['appointment_time'] = $data1->appointment_time;
            $smsArr['time_slot'] = $time_slot;
            $smsArr['slot_no'] = $data1->slot_no;
            $smsArr['patient_contact_no'] = $user->patient_contact_no;
            $smsArr['clinic_no'] = stripslashes($clinic->clinic_contact_no);
            if($state==1){
                $smsArr['config_key'] = 'SMS_TEXT_APP';
            }
            else if($state==2){
                $smsArr['config_key'] = 'SMS_TEXT_APP_REJECT';
            }
            
            $sendsms = AppHelper::send_sms($smsArr);
        }
        DB::table('appointment_details')->where('appointment_id',$id)->update(array('approve_status' => $status));
        $flag=$status+1;
        
        return Redirect::action('DoctorController@anyShowAppointmentList', array($flag));

        }
        else if($state==2)
        {
            
            $status=0;
            $user_message="<br>Sorry. Your appointment for the clinic <b>".stripslashes($clinic->clinic_name)."</b> to consult <b>Dr.".$doctor->doctor_profile_name."</b> on <b>".date('d/M/Y l',strtotime($data1->appointment_time))." (".$time_slot.")</b> is cancelled. Please try another date.";
              
        
        if(Swift_Validate::email($patient->user_email_id)){
           Mail::send('mailtemplates.appointmenttemplate', array('first_name'=> $user->patient_fname,'last_name'=>$user->patient_lname,'user_message'=>$user_message), function($message) use ($patient){
                $message->to($patient->user_email_id)->subject('Appointment Info');
            });
            
            $smsArr = array();
            $smsArr['patient_name'] = $user->patient_fname.' '.$user->patient_lname;
            $smsArr['clinic_name'] = stripslashes($clinic->clinic_name);
            $smsArr['doctor_name'] = $doctor->doctor_profile_name;
            $smsArr['appointment_time'] = $data1->appointment_time;
            $smsArr['time_slot'] = $time_slot;
            $smsArr['slot_no'] = $data1->slot_no;
            $smsArr['patient_contact_no'] = $user->patient_contact_no;
            $smsArr['clinic_no'] = stripslashes($clinic->clinic_contact_no);
            if($state==1){
                $smsArr['config_key'] = 'SMS_TEXT_APP';
            }
            else if($state==2){
                $smsArr['config_key'] = 'SMS_TEXT_APP_REJECT';
            }
            
            $sendsms = AppHelper::send_sms($smsArr);
        }
        
       
        DB::table('appointment_details')->where('appointment_id','=',$id)->delete(); 
     
         $flag=$status+1;
        return Redirect::action('DoctorController@anyShowAppointmentList', array($flag));
        }
             
      }
      
      else{
                 $flag=5;
        return Redirect::action('DoctorController@anyShowAppointmentList', array($flag));
             }
    }

//  ---Approve appointments of selected appointments     
    public function postApproveAppointmentAll()
    {
        $check=Input::get('check');
        for($i=0;$i<count($check);$i++)
        {
                DB::table('appointment_details')->where('appointment_id','=',$check[$i])->update(array('approve_status'=> '1'));

                $data1= Appointmentdetails::where('appointment_id','=',$check[$i])->first();
                $user=Patient::where('patient_id','=',$data1->patient_id)->first();
                $clinic=Doctorsclinicdetails::where('clinic_id','=',$data1->clinic_id)->first();
                $patient=User::where('user_id','=',$user->user_id)->first();
                $user_message="<br>Your appointment for the clinic <b>' ".$clinic->clinic_name." '</b> is scheduled for <b>' ".date('d/M/Y l',strtotime($data1->appointment_time))." '</b> . For confirm your appointment please call: ".$clinic->clinic_name." , ".$clinic->clinic_contact_no;

                $message = "Dear ".$user->patient_fname." ".$user->patient_lname."<br>".$user_message."<br><br>Kind Regards<br>Bookmyconsult Team";
                $from = "bookmyconsult@gmail.com";
                $headers = 'MIME-Version: 1.0' . "\r\n";
                $headers .= 'Content-Type: text/html; charset=ISO-8859-1' . "\r\n";
                $headers .= "From:Bookmyconsult<bookmyconsult@gmail.com>\r\n";
                mail($patient->user_email_id, 'Appointment Info', $message, $headers);

        }

        if(count($check)==0)
            $flag='22';
        else
            $flag='11';
        return Redirect::action('DoctorController@anyShowAppointmentList', array($flag));
           
    }
     
        
//      ---Show page for listing blogs
        public function anyShowBlogList($flag='')   
        {
            $view_msg='';
            if($flag==1)
                $view_msg='1 Blog Deleted';
            else if($flag==2)
                $view_msg='1 Blog Added';
            else if($flag==3)
                $view_msg='1 Blog Modified';
            else if($flag==4)
                $view_msg='1 Blog Reset';
            else if($flag==5)
                $view_msg='1 Blog Published';
            
            Session::put('side_bar_active','Blogs');
            return View::make('blog.doctor_blog_list_new')->with(array('view_msg'=>$view_msg));
        }
        
//      ---Datatable for listing blogs        
        public function anyBloglist()
        {          
            $blog=Blogs::where('written_doctor_id','=',Session::get('doctor_id'))->get();
            $cnt=$blog->count();
            if($cnt>0)
            {
               foreach($blog as $data)
               {
                   $row1=$data['blog_title'];
                   $row2=date('d-M-Y',strtotime($data['updated_at']));

                   if($data['approved']==0) 
                   {    
                       $row3= 'No';
                   }
                   else 
                   {
                       $row3= 'Yes';
                   }

                   if($data['status']==0) 
                   {    
                       $icon=asset("images/wrong.jpeg");
                       $row4=html_entity_decode(link_to_action('DoctorController@getBtoggle', '<img src="'.$icon.'"alt=asknow width=20 height=20/>',array($data['blog_id']),array('title'=>'Click Me For Publishing')));

                   }
                   else 
                   {
                       $icon=asset("images/tick.jpeg");
                       $row4=html_entity_decode(link_to_action('DoctorController@getBtoggle', '<img src="'.$icon.'"alt=asknow width=20 height=20/>',array($data['blog_id']),array('title'=>'Click Me For Hiding')));

                   }

                   $row5=html_entity_decode(link_to_action('DoctorController@anyShowEditBlog', '<img src="'.asset("images/edit.jpeg").'"alt=asknow width=20 height=20/>',array($data['blog_id'])));

                   $row6="<span style='cursor:pointer;'><img src=".asset('images/del2.jpeg')." alt=asknow width=20 height=20 onclick=Confirm(".$data['blog_id'].") /></span>";                       
                   $output['aaData'][] = array($row1,$row2,$row3,$row4,$row5,$row6);                      
              }
            }
            else
            {
                $output = array('aaData' => array());
            }
            echo json_encode($output);    
        }
        
//      ---Show page for add/edit the selected blog        
        public function anyShowEditBlog($id=0,$flag='') 
        {
            $error='';
            if($flag==1) 
                $error="Description can't be null";      
                Session::put('side_bar_active','Blogs');
                return View::make('blog.doctor_blog_single_new')->with(array('blog_id' => $id,'error'=>$error));
        }
        
//      ---Add/Edit blog           
        public function anyEditBlog($id)
        {
             if(Input::get('blog_description')=='')
             {
                 return Redirect::action('DoctorController@anyShowEditBlog', array($id,1));

             }
             $data['blog_id']=Input::get('blog_id');
             $data['blog_title']=ucfirst(Input::get('blog_title'));
             $data['blog_about']=ucfirst(Input::get('blog_about'));
             $data['blog_description']=Input::get('blog_description');
             $data['status']=Input::get('status');
             $data['written_doctor_id']=Session::get('doctor_id');
             $data['approved']=0;
             $now = date('Y-m-d H:i:s');
             
            if (Input::hasFile('profile_picture'))
            {
              $file = Input::file('profile_picture');
              $file_org = $file->getClientOriginalName();
              $file_name = Str::random(10).'_'.$file_org;
             
              $destinationPath = 'images/upload/';
              $uploadSuccess = Input::file('profile_picture')->move($destinationPath, $file_name);
              $file_path = $destinationPath.$file_name;
              if($uploadSuccess)
              {
                $data['blog_picture']= $file_name;
              }  
            }
            else{
                $data['blog_picture'] = Input::get('picture_hidden');
            }
             
            if($id==0)
            {             
               $data['created_at']=$now;
               $data['updated_at']=$now;
               Blogs::InsertGetId($data);
               $flag=2;
            }
            else
            {
                $data['updated_at']=$now;
                Blogs::where('blog_id','=',$id)->update($data);    
                $flag=3;
            }
            return Redirect::action('DoctorController@anyShowBlogList', array($flag));
            
        }        
        
//      ---toggle publication staus of blogs        
        public function getBtoggle($id) 
        {
            $data=Blogs::where('blog_id','=',$id)->first();
            $flag=$data->status;
            if($flag==1)
            {
                $status=0;$flag=4;
            }
            else 
            {
                $status=1;$flag=5;
            }
            Blogs::where('blog_id','=',$id)->update(array('status' => $status));
            return Redirect::action('DoctorController@anyShowBlogList', array($flag));        }
        
//      ---Remove Selected Blog        
        public function anyRemoveblogpage($id=0)
        {
               DB::table('blogs')->where('blog_id','=',$id)->delete();
               return Redirect::action('DoctorController@anyShowBlogList',array(1));       
        }
        
//        public function anyShowUpdatePwd($flag='')
//        {
//            $msg='';
//            if($flag==1)
//                $msg='Password Updated';
//            else if($flag==2)
//                $msg="Current Password Is Wrong";
//            return View::make('doctor_details.doctor_update_pwd')->with('msg',$msg);
//        }
        
/****************************************************************************************************************************************************/

//      show the page for listing messages
        public function anyShowMessages()
        {
            return View::make('feedback.doctor_messages_list')->with('msg','');
        }
        
//      Datatable for listing messages      
        public function anyMessagelist()
        {            
            $msg= Usermessages::where('doctor_id','=',Session::get('doctor_id'))->orderby('message_send_date','desc')->get();
            if($msg->count()>0)
            {
               foreach($msg as $data)
               {
                    $name=$data['user_name'];
                    $email=$data['user_email'];
                    $message=substr($data['message_subject'],0,25);
                    if($message=='')$message='(no subject)';
                    $time=date('d-m-Y H:i:s',strtotime($data['message_send_date']));
                    $del= "<img src=".asset('images/del2.jpeg')." title=Delete style='cursor:pointer;' width=20 height=20 onclick=Confirm(".$data['message_id'].") />";
                    if($data['read_status']==0)
                    {
                       $name= "<b>".link_to_action('DoctorController@anyShowMailPage', $name,array($data['message_id']),array('title'=>'Show Details'))."</b>";
                       $email= "<b>".link_to_action('DoctorController@anyShowMailPage', $email,array($data['message_id']),array('title'=>'Show Details'))."</b>";
                       $message= "<b>".link_to_action('DoctorController@anyShowMailPage', $message,array($data['message_id']),array('title'=>'Show Details'))."</b>";
                       $time= "<b>".link_to_action('DoctorController@anyShowMailPage', $time,array($data['message_id']),array('title'=>'Show Details'))."</b>";
                    }
                    else
                    {
                       $name= link_to_action('DoctorController@anyShowMailPage', $name,array($data['message_id']),array('title'=>'Show Details'));
                       $email=link_to_action('DoctorController@anyShowMailPage', $email,array($data['message_id']),array('title'=>'Show Details'));
                       $message=link_to_action('DoctorController@anyShowMailPage', $message,array($data['message_id']),array('title'=>'Show Details'));
                       $time= link_to_action('DoctorController@anyShowMailPage', $time,array($data['message_id']),array('title'=>'Show Details'));
                    }    
                    $output['aaData'][] = array($name,$email,$message,$time,$del);     
               }
            }
            else
            {
                $output = array('aaData' => array());
            }
            echo json_encode($output);    
        }  
        
 //     Remove Selected Message        
        public function anyRemovemessage($id=0)
        {

               DB::table('user_messages')->where('message_id','=',$id)->delete();
               $view_msg='1 Message Deleted';
               return View::make('feedback.doctor_messages_list')->with('msg',$view_msg);      
        }
        
//      Show the page for single mail that also support reply        
        public function anyShowMailPage($message_id)
        {
            DB::table('user_messages')->where('message_id','=',$message_id)->update(array('read_status' =>1));
            return View::make('feedback.doctor_message_single')->with(array('message_id' => $message_id));
        }
        
//      Send reply to user mail        
        public function postSendReply()
        {
            $message_id=Input::get('message_id');
            $user_message=Input::get('reply');
                        
            DB::table('user_messages')->where('message_id','=',$message_id)->update(array('doctor_reply' =>$user_message));
            
            $user_data=  Usermessages::where('message_id','=',$message_id)->first();
            $message = 'Dear '.$user_data['user_name']."<br><br>".$user_message."<br><br>Kind Regards<br>Bookmyconsult Team";
            $from = "bookmyconsult@gmail.com";
            $headers = 'MIME-Version: 1.0' . "\r\n";
            $headers .= 'Content-Type: text/html; charset=ISO-8859-1' . "\r\n";
            $headers .= "From:Bookmyconsult<bookmyconsult@gmail.com>\r\n";
            mail($user_data['user_email'], 'Bookmyconsult', $message, $headers);
            
            return View::make('feedback.doctor_messages_list')->with('msg','Your Mail Send Successfully');
        }
       
	public function anyShowDoctorUpdatePassword($flag=''){
        
	$data['msg']='';
	if($flag==1)
	    $data['msg']='Password updated successfully';
        Session::put('side_bar_active','Password');
	return View::make('doctor_details.doctor_update_password_new',$data);
	
    }
    
    public function postSaveDoctorPassword(){
	$rules = array(
	    'current_password'     =>      'required',
	    'new_password'     =>      'required',
	    'confirm_password'    =>      'required|same:new_password',
	    );

	$validator = Validator::make(Input::all(), $rules);
	if ($validator->passes()){
	    $Doctor = DB::table('doctors')->where('doctor_id', Session::get('doctor_id'))->first();
	    $user = DB::table('users')->where('user_id', $Doctor->user_id)->first();
	    
	    if ($user->user_password == md5(Input::get('current_password'))) {
	        DB::table('users')->where('user_id', $Doctor->user_id)
	                       ->update(array('user_password' => md5(Input::get('new_password'))));
	        
//                Session::flash('success_msg','Password updated succcessfully');
	        return Redirect::action('DoctorController@anyShowFeed');
	    }
	    else{
	        Session::flash('current_msg','Current Password entered is Incorrect');
	        return Redirect::action('DoctorController@anyShowDoctorUpdatePassword')->withErrors($validator->messages())->withInput();
	    }   
	}
	else{
	   return Redirect::action('DoctorController@anyShowDoctorUpdatePassword')->withErrors($validator->messages())->withInput();
	}
    }
            
//    //     Remove Selected Message        
//    public function anyRemovecomments($id=0)
//    {
//
//           DB::table('user_comments')->where('comment_id','=',$id)->update(array('approve_status' => 1));
//           $view_msg='1 Comment Approved';
//           return View::make('feedback.doctor_comments_list')->with('msg',$view_msg);      
//    }
    //      ---show doctor signup page        
//        public function anyShowDoctorSignPage()
//        {	
//            echo 'g';exit;
////            return View::make('signup.doctor_signup');
//        }
        
//      goto doctor profile page for filling details
//        public function anyRegister()   
//        {
//            $rules = array('user_email_id'   => 'unique:users');
//            $validator = Validator::make(Input::all(), $rules);
//            if ($validator->passes()) 
//            {
//		    $user = new User(array('user_email_id'=> Input::get('user_email_id'),'user_password' => md5(Input::get('user_password')),'user_type' => 0));
//                    $user->save();
//                    $data=array('user_id'=>$user->user_id);
//		    DB::table('doctors')->insertGetId($data);
//                    $doctor=  Doctors::where('user_id','=',$user->user_id)->first();
//                    Session::put('user_id', $user->user_id);   
//                    Session::put('user_type',0);   
//                    Session::put('doctor_id',$doctor['doctor_id']);   
//        
//                   if(Session::get('user_id')!='' && Session::get('user_type')==0)
//                   {  		      
//                        return View::make('signup.doctor_profile_filling');
//                   }
//                   else
//                   {                        
//                       return Redirect::to('signup/doctor')->withErrors($validator);
//                   }							
//            } 
//            else
//            {
//                return Redirect::to('signup/doctor')->withErrors($validator);
//            }      
//        }
        
 
////      final step of signup that goto success message
//        public function anyFinalRegister() 
//        {
//                $tags='';
//                if(Input::get('my-select'))
//                {
//                    $tags=implode(',',Input::get('my-select'));
//                }
//                if(Input::get('other_tags'))
//                {
//                    if($tags!='')
//                    {
//                        $tags.=",".Input::get('other_tags');
//                    }
//                    else
//                    {
//                        $tags=Input::get('other_tags');
//                    }
//                }
//                DB::table('doctors_interest')->where('doctor_id','=',Session::get('doctor_id'))->update(array('doctor_speciality_details'=> Input::get('description')));
//                DB::table('doctors')->where('doctor_id','=',Session::get('doctor_id'))->update(array('interested_tag_names'=>$tags));
//                return View::make('signup.doctor_success_msg');
//        }    
//        public function doctorSignup()
//        {
//                $date[0]='-Day-';
//                for($i=1; $i <= 31; $i++)
//                $date[$i]=$i;
//
//                $year[0]='-Year-';
//                for($j=date("Y"); $j >=date("Y")-100; $j--)
//                $year[$j]=$j;
//
//                $month[0]='-Month-'; 
//                for ($m=1; $m<=12; $m++)
//                $month[$m] = date('F', mktime(0,0,0,$m, 1, date('Y')));
//                return View::make('signup.doctor_signup',array('date_list' => $date,'year_list'=>$year,'month_list'=>$month));
//        }   
//  
}

