<?php
class ExtractprojectsController extends Zend_Controller_Action
{
    
    private $_startTime;

    protected $ProjectList = array();
    
    protected $ProjectFiles = array();
    
    protected $ProjectListFields =
        array('external_url' => '',
        'external_id' => '',
        'title' => '',
        'description' => '',
        'posted' => '',
        'ends' => '0000-00-00 00:00:00',
        'budget_low' => 0,
        'budget_high' => 0,
        'budget_currency' => 1,
        'active' => 1, 
        'jobtype' => 3,
        'bids' => 0,
        'bids_avg' => 0,
        'external_user_id' => '',
        'category_id' => null
    );
    
    public function preDispatch()
    {
        $this->_startTime = microtime(true);
    }
    
    public function postDispatch()
    {
        $this->getResponse()->setBody(round(microtime(true) - $this->_startTime, 4));
    }
    
    public function fromelanceAction()
    {
		
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
        $urlTemplate = "https://api.elance.com/api2/jobs?access_token=%access_token%"
            . "&sortCol=postedDate&sortOrder=desc&page=%page%&rpp=50";
        
		
		
        $projectModel = new Application_Model_DbTable_Projects();
        $platformModel = new Application_Model_DbTable_Platforms();
        $platformCategoryModel = new Application_Model_DbTable_PlatformCategories();
        
        $platform = $platformModel->find(Application_Model_DbTable_Platforms::ELANCE_ID)->current();
		
		//var_dump($platform);	die('here1');
        
        $apiKeys = json_decode($platform->api_keys);
		
		//echo '<br><br> ## $apiKeys ## <br>';
		//var_dump($apiKeys);
		
        if (empty($apiKeys->client_id) || empty($apiKeys->client_secret)) {
            throw new Exception('Empty elance API keys');
        }
        
		//echo '<br><br> ## $platform->tokens ## <br>';
		//var_dump($platform->tokens);
		
		
        $tokens = (array)json_decode($platform->tokens);
        $needsRefresh = false;
        $needsRetoken = false;
		
		if (!isset($tokens['access_token'])) {
            $needsRetoken = true;
        }
		
		//echo '<br><br> ## $needsRetoken ## <br>';		
		//var_dump($needsRetoken);
		
        //echo '<br><br> ## time() ## <br>';		
		//var_dump(time());
		
        if (!$needsRetoken && isset($tokens['expires_in']) && time() >= $tokens['expires_in']) {
            $needsRefresh = true;
        }
		
		//echo '<br><br> ## $needsRefresh ## <br>';
		//var_dump($needsRefresh);
        
        if (!$needsRetoken && !$needsRefresh) {
            $url = str_replace(array('%access_token%', '%page%'), array($tokens['access_token'], 1), $urlTemplate);
            $pageResult = file_get_contents($url, $useIncludePath = false);
            if ($pageResult) {
                $pageResult = json_decode($pageResult);
            }
            if (!$pageResult || !isset($pageResult->data)) {
                $needsRefresh = true;
            }
        }
		
        //echo '<br><br> ## $tokens[refresh_token] ## <br>';		
		//var_dump($tokens['refresh_token']);
		
        if ($needsRefresh && isset($tokens['refresh_token'])) {
            $context = stream_context_create(array(
                'http' => array(
                    'method' => 'POST',
                    'header' => 'Content-Type: application/x-www-form-urlencoded' . PHP_EOL,
                    'content' => http_build_query(array(
                        'refresh_token' => $tokens['refresh_token'],
                        'grant_type' => 'refresh_token',
                        'client_id' => $apiKeys->client_id,
                        'client_secret' => $apiKeys->client_secret
                    ))
                )
            ));
            
			//echo '<br><br> ## $context ## <br>';		
			//var_dump($context);
			
            $result = file_get_contents('https://api.elance.com/api2/oauth/token', $useIncludePath = false, $context);
            
			//echo '<br><br> ## $result ## <br>';		
			//var_dump($result);
			
			if ($result) {
                $result = json_decode($result);
            }
			
			
			//echo '<br><br> ## json_decode($result) ## <br>';		
			//var_dump($result);
            
            if (!$result || empty($result->data->access_token)) {
                $needsRetoken = true;
            } else {
                $tokens['access_token'] = $result->data->access_token;
                $tokens['expires_in'] = $result->data->expires_in;
                $tokens['refresh_token'] = $result->data->refresh_token;
                $platform->tokens = json_encode($tokens);
                $platform->save();
            }
        }
		
		//echo '<br><br> ## $needsRetoken ## <br>';		
		//var_dump($needsRetoken);
        
        if ($needsRetoken) {
            $context = stream_context_create(array(
                'http' => array(
                    'method' => 'POST',
                    'header' => 'Content-Type: application/x-www-form-urlencoded' . PHP_EOL,
                    'content' => http_build_query(array(
                        'grant_type' => 'client_credentials',
                        'client_id' => $apiKeys->client_id,
                        'client_secret' => $apiKeys->client_secret
                    ))
                )
            ));
            
            $result = file_get_contents('https://api.elance.com/api2/oauth/token', $useIncludePath = false, $context);
            
			//echo '<br><br> ## $result ## <br>';		
			//var_dump($result);
			
			if ($result) {
                $result = json_decode($result);
            }
			
			//echo '<br><br> ## json_decode($result) ## <br>';		
			//var_dump($result);
			
            if (!$result || empty($result->data->access_token)) {
                throw new Exception('Access token is not given');
            } else {
                $tokens['access_token'] = $result->data->access_token;
                $tokens['expires_in'] = $result->data->expires_in;
                $tokens['refresh_token'] = $result->data->refresh_token;
                $platform->tokens = json_encode($tokens);
                $platform->save();
            }
        }
		
		//echo '<br><br> ## $tokens[access_token] ## <br>';		
		//var_dump($tokens['access_token']);
        
        if (isset($tokens['access_token'])) {
            $lastProject = $projectModel->getLast(Application_Model_DbTable_Platforms::ELANCE_ID);
            $lastProjectDate = null === $lastProject ? null : strtotime($lastProject->posted);
            
            $page = isset($pageResult->data) ? 2 : 1;
            $pageLimit = 20;
            $terminate = false;
            do {
                if (empty($pageResult)) {
                    $url = str_replace(array('%access_token%', '%page%'), array($tokens['access_token'], $page), $urlTemplate);
                    
					//echo '<br><br> ## $url ## <br>';		
					//var_dump($url);
					
					$pageResult = json_decode(file_get_contents($url));
                    $page++;
                }
                
				//echo '<br><br> ## $pageResult->data->pageResults ## <br>';
				//echo "<pre>";		
				//var_dump($pageResult->data->pageResults); die;
				
                foreach ($pageResult->data->pageResults as $item) {
                    if (!$terminate && $item->postedDate <= $lastProjectDate) {
                        $terminate = true;
                        $pageLimit = $page + 1;
                    }
                    
                    $project = $this->ProjectListFields;
                    
                    $project['external_url'] = $item->jobURL;
                    $project['external_id'] = $item->jobId;
                    $project['title'] = $item->name;
                    $project['description'] = $item->description;
                    $project['posted'] = date('Y-m-d H:i:s', $item->postedDate);
                    $project['ends'] = date('Y-m-d H:i:s', $item->endDate);
                    $project['bids'] = (int)$item->numProposals;
                    $project['category_id'] = $platformCategoryModel->getCategoryId(Application_Model_DbTable_Platforms::ELANCE_ID, $item->jobCatId);
                    
                    if ($item->isHourly) {
                        $project['budget_low'] = (float)$item->hourlyRateMin;
                        $project['budget_high'] = (float)$item->hourlyRateMax;
                        $project['jobtype'] = 1;
                    } else {
                        $project['budget_low'] = (float)$item->budgetMin;
                        $project['budget_high'] = (float)$item->budgetMax;
                        $project['jobtype'] = 2;
                    }
                    //echo "<pre>";
					//print_r($project);die;
                    $projectModel->importProject($project, Application_Model_DbTable_Platforms::ELANCE_ID);
                }
                
                $pageResult = null;
            } while ($page < $pageLimit);
        }
    }
    
    public function fromfreelanceAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
        $urlTemplate = 'http://www.freelance.com/en/search/mission?d-447263-n=1&d-447263-o=1&d-447263-p=%page%&d-447263-s=POST_DATE';
        
        $projectModel = new Application_Model_DbTable_Projects();
        
        $doc = new DOMDocument();
        
        for ($page = 1; $page <= 3; $page++) {
            $url = str_replace('%page%', $page, $urlTemplate);
            $content = file_get_contents($url, $useIncludePath = false);
            
            // hide a lot of HTML DOM validation warnings
            @$doc->loadHTML($content);
            $xpath = new DOMXPath($doc);
            
            $items = $xpath->query('body/div/div/div/div/table[@id="result"]/tbody/tr');
            foreach ($items as $item) {
                $project = $this->ProjectListFields;
                
                $linkElement = $xpath->query('td[@class="desc"]/strong/a', $item)->item(0);
                $project['external_url'] = 'http://www.freelance.com' . $linkElement->attributes->item(0)->nodeValue;
                
                $context = stream_context_create(array(
                    'http'=>array(
                        'method'=>"GET",
                        'follow_location' => '0'
                    )
                ));
                $content = file_get_contents($project['external_url'], $useIncludePath = false, $context);
                if (!$content) {
                    continue;
                }
                
                $project['posted'] = date('Y-m-d H:i:s', strtotime(trim($xpath->query('td[@class="date"]', $item)->item(0)->nodeValue)));
                $project['title'] = $linkElement->nodeValue;
                $project['external_id'] = mb_substr($project['external_url'], mb_strrpos($project['external_url'], '/') + 1);
                $price = trim($xpath->query('td[@class="budget"]', $item)->item(0)->nodeValue);
                if (preg_match('#^([\d\.]+)[^\d]*?–[^\d]*?([\d\.]+).*?\$/(project|month|hour)$#umis', $price, $matches)) {
                    $project['budget_low'] = (float)$matches[1];
                    $project['budget_high'] = (float)$matches[2];
                    $project['jobtype'] = array_search($matches[3], array(1 => 'hour', 2 => 'project', 4 => 'month'));
                }
                $subdoc = new DOMDocument();
                @$subdoc->loadHTML($content); // hide a lot of HTML DOM validation warnings
                $subxpath = new DOMXPath($subdoc);
                
                $project['description'] = trim($subxpath->query('body/div/div/div/div/div[@class="wysiwygWrapper highlightable"]')->item(0)->nodeValue);
                $project['external_second_id'] = trim($subxpath->query('body/div/h2[@class="header-c hc-c"]/span[@class="line-a"]/strong')->item(0)->nodeValue);
                
                $projectModel->importProject($project, Application_Model_DbTable_Platforms::FREELANCE_ID);
            }
        }
    }
    
    public function fromodeskAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
        $categoryIds = array(
            1 => 'Web Development',
            2 => 'Writing & Translation',
            3 => 'Customer Service',
            4 => 'Software Development',
            5 => 'Administrative Support',
            6 => 'Sales & Marketing',
            7 => 'Networking & Information Systems',
            8 => 'Design & Multimedia',
            9 => 'Business Services'
        );
        
        $projectModel = new Application_Model_DbTable_Projects();
        $platformCategoryModel = new Application_Model_DbTable_PlatformCategories();
        
        $lastProject = $projectModel->getLast(Application_Model_DbTable_Platforms::ODESK_ID);
        $lastProjectDate = null === $lastProject ? null : strtotime($lastProject->posted);
        
        $doc = new DOMDocument();
        
        $difference = -4 * 3600;
        
        $pageLimit = 5;
        $terminate = false;
        for ($page = 0; $page < $pageLimit; $page++) {
            $offset = $page * 200;
            $source = file_get_contents("https://www.odesk.com/api/profiles/v1/search/jobs.xml?page={$offset};200&st='Open'");
            $doc->loadXML($source);
            $xpath = new DOMXpath($doc);

            $items = $xpath->query("jobs/job");
            foreach ($items as $item) {
                $project = $this->ProjectListFields;
                
                $datePosted = $xpath->query("date_posted", $item)->item(0)->nodeValue;
                $timePosted = $xpath->query("op_time_posted", $item)->item(0)->nodeValue;
                $timestampPosted = strtotime($datePosted . ' ' . $timePosted);
                if (!$terminate && $timestampPosted <= $lastProjectDate) {
                    $terminate = true;
                    $pageLimit = $page + 2;
                }
                $jobtype = $xpath->query("job_type", $item)->item(0)->nodeValue;
                
                $project['title'] = $xpath->query("op_title", $item)->item(0)->nodeValue;
                $project['description'] = $xpath->query("op_description", $item)->item(0)->nodeValue;
                if ($jobtype == 'Hourly') {
                    $project['budget_low'] = (float)$xpath->query("op_pref_hourly_rate_min", $item)->item(0)->nodeValue;
                    $project['budget_high'] = (float)$xpath->query("op_pref_hourly_rate_max", $item)->item(0)->nodeValue;
                } else {
                    $project['budget_low'] = (float)$xpath->query("amount", $item)->item(0)->nodeValue;
                    $project['budget_high'] = (float)$xpath->query("amount", $item)->item(0)->nodeValue;
                }
                $project['bids'] = (int)$xpath->query('op_tot_cand', $item)->item(0)->nodeValue;
                $project['bids_avg'] = (float)$xpath->query("op_avg_bid_all", $item)->item(0)->nodeValue;
                $project['posted'] = date('Y-m-d H:i:s', $timestampPosted + $difference);
                $project['external_second_id'] = $xpath->query("op_recno", $item)->item(0)->nodeValue;
                $categoryId = array_search($xpath->query('job_category_level_one', $item)->item(0)->nodeValue, $categoryIds);
                $project['category_id'] = $platformCategoryModel->getCategoryId(Application_Model_DbTable_Platforms::ODESK_ID, $categoryId);
                
                $project['external_id'] = $xpath->query("ciphertext", $item)->item(0)->nodeValue;
                $project['external_url'] = 'https://www.odesk.com/jobs/1_' . $project['external_id'];
                $project['jobtype'] = $jobtype == 'Hourly' ? 1 : 2;
                
                $files = array();
                $fileUrl = $xpath->query("op_attached_doc", $item)->item(0)->nodeValue;
                if ($fileUrl) {
                    $files[] = array(
                        'file_url' => $xpath->query("op_attached_doc", $item)->item(0)->nodeValue
                    );
                }
                
                $projectModel->importProject($project, Application_Model_DbTable_Platforms::ODESK_ID, $files);
            }
        }
    }
    
    public function frompeopleperhourAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
        include_once('HTMLParser/simple_html_dom.php');
        
        $projectModel = new Application_Model_DbTable_Projects();
        
        $CurrencyArray = array('GBP'=>3,'EUR'=>2,'USD'=>1,'$'=>1,'€'=>2,'£'=>3);
        $PPH_Project_Types = array('fixed_price'=>2,'hourly'=>1);
        $_pph_conf = 'var __PPH_CONF_ = ';
        $_pph_conf_end = '/*]]>*/';
        
        $lastProject = $projectModel->fetchRow('platform_id = ' . (int)Application_Model_DbTable_Platforms::PEOPLEPERHOUR_ID, 'external_id DESC');
        $lastProjectId = null === $lastProject ? null : $lastProject->external_id;
        
        $lastPage = 5;
        $terminate = false;
        for ($page = 1; $page <= $lastPage; $page++) {
            $html = file_get_html('http://www.peopleperhour.com/freelance-jobs?sort=latest&page='.$page);
            foreach($html->find('div.hoverable') as $fli) {
                $project = $this->ProjectListFields;
                $files = array();
                
                $BoxModelFix = $fli->find('div.boxmodelfix',0);
                
                $a_job = $BoxModelFix->find('div.title',0)->find('h3',0)->find('a.job',0);
                
                $project['external_url'] = $a_job->href;
                $project['title'] = $a_job->plaintext;
                
                $job_html = file_get_html($project['external_url']);
                
                $_PPH_CONF_START_POS =  mb_strpos($job_html,$_pph_conf,0,'utf-8');
                $_PPH_CONF_END_POS = mb_strpos($job_html,$_pph_conf_end,$_PPH_CONF_START_POS,'utf-8');
                
                $PPHProjInfo =  mb_substr( $job_html , $_PPH_CONF_START_POS + strlen($_pph_conf) - 1 , $_PPH_CONF_END_POS - $_PPH_CONF_START_POS - strlen($_pph_conf) , 'utf-8');
                $PPHProjInfo = rtrim($PPHProjInfo, '; ');
                $PPHProjInfo = str_replace("'","\"",$PPHProjInfo);
                $PPHProjInfoArray = json_decode($PPHProjInfo, true);
                
                $innerText = $BoxModelFix->innertext;
                if (preg_match('|<time class="crop" title="[^"]+">([^<]+)</time>|iums', $innerText, $matches)) {
                    $project['posted'] = date('Y-m-d H:i:s', strtotime($matches[1]));
                }
                if($PPHProjInfoArray) {
                    $project['external_id'] = $PPHProjInfoArray['modules']['StreamProposalAttachment']['proj_id'];
                    if (!$terminate && $project['external_id'] <= $lastProjectId) {
                        $lastPage = $page + 1;
                        $terminate = true;
                    }
                    if (isset($PPH_Project_Types[$PPHProjInfoArray['modules']['StreamProposalAttachment']['projectType']])) {
                        $project['jobtype'] = $PPH_Project_Types[$PPHProjInfoArray['modules']['StreamProposalAttachment']['projectType']];
                    }
                    $project['budget_low'] = $project['budget_high'] = (float) $PPHProjInfoArray['modules']['StreamProposalAttachment']['budget'];
                    if (isset($CurrencyArray[$PPHProjInfoArray['currency']])) {
                        $project['budget_currency'] = $CurrencyArray[$PPHProjInfoArray['currency']];
                    }
                    $project['description'] = $job_html->find('div.main-content',0)->find('div.content-text',0)->innertext();
                    
                    $filescontainer =  $job_html->find('div.main-content',0)->find('div.clearfix',4)->find('div.left',0);
                    if ($filescontainer) {
                        foreach ($filescontainer->find('a.attach-filename') as $attach_a) {
                            $files[] = array(
                                'file_name' => $attach_a->innertext(),
                                'file_url' => $attach_a->getAttribute('href')
                            );
                        }
                    }
                }
                
                $projectModel->importProject($project, Application_Model_DbTable_Platforms::PEOPLEPERHOUR_ID, $files);
            }
        }
    }
    
    public function fromfreelancerAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
        $projectModel = new Application_Model_DbTable_Projects();
        
        $platformCategoryModel = new Application_Model_DbTable_PlatformCategories();
        
        $lastProject = $projectModel->getLast(Application_Model_DbTable_Platforms::FREELANCER_ID);
        $lastProjectDate = null === $lastProject ? null : strtotime($lastProject->posted);
        ini_set ('allow_url_fopen', '1');
        $pageLimit = 3;
        $terminate = false;
        for ($page = 0; $page < $pageLimit; $page++) {
            $source = file_get_contents('http://api.freelancer.com/Project/Search.json?order=submitdate&order_dir=desc&pg=' . $page);
            $items = json_decode($source);
            
            foreach ($items->projects->items as $item) {
                /*if (!$terminate && $item->start_unixtime <= $lastProjectDate) {
                    $terminate = true;
                    $pageLimit = $page += 2;
                }*/
                
                $project = $this->ProjectListFields;
                
                $projectPage = file_get_contents('http://api.freelancer.com/Project/' . (int)$item->id . '.json');
                $projectInfo = json_decode($projectPage);
                if (empty($projectInfo->project)) {
                    continue;
                }
                $project['jobtype'] = $projectInfo->project->isHourly ? 1 : 2;
                
                $project['external_id'] = $item->id;
                $project['title'] = $item->name;
                if (!$project['title']) {
                    continue;
                }
                $project['description'] = $item->short_descr;
                $project['external_url'] = $item->url;
                $project['posted'] = date('Y-m-d H:i:s'); // , $item->start_unixtime
                $project['ends'] = date('Y-m-d H:i:s', $item->end_unixtime);
                $project['budget_low'] = (float)$item->budget->min;
                $project['budget_high'] = (float)$item->budget->max;
                $project['bids'] = (int)$item->bid_stats->count;
                $project['bids_avg'] = (int)$item->bid_stats->avg;
                
                if ($page == 0 && substr($project['description'], -3) === '...') {
                    $projectHTML = file_get_contents($project['external_url']);
                    if (preg_match('|<span class="bold margin-b5">Project Description: </span><br />(.*?)</p>|umis', $projectHTML, $matches)) {
                        $project['description'] = strip_tags(str_replace('<br>', "\n", $matches[1]));
                    }
                    sleep(2);
                }
                
                $categoryIds = array();
                foreach ($item->jobsDetails as $detail) {
                    if (!isset($categoryIds[$detail->category_id])) {
                        $categoryIds[$detail->category_id] = 0;
                    }
                    $categoryIds[$detail->category_id]++;
                }
                if (count($categoryIds)) {
                    arsort($categoryIds);
                    reset($categoryIds);
                    $categoryId = key($categoryIds);
                    $project['category_id'] = $platformCategoryModel->getCategoryId(Application_Model_DbTable_Platforms::FREELANCER_ID, $categoryId);
                }
                
                $files = array();
                if ($item->files) {
                    foreach($item->files as $fileKey => $fileValue) {
                        $files[] = array(
                            'file_name' => $fileValue->name,
                            'file_url' => $fileValue->full_file_url
                        );
                    }
                }
                $projectModel->importProject($project, Application_Model_DbTable_Platforms::FREELANCER_ID, $files);
                
                sleep(2);
            }
        }
    }
    
    public function fromdesigncrowdAction()
    {
		$this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
        $projectModel = new Application_Model_DbTable_Projects();
        
        $currencies = array(1=>'$', 2=>'€', 3=>'£');
        
        $lastProject = $projectModel->getLast(Application_Model_DbTable_Platforms::DESIGNCROWD_ID);
        $lastProjectDate = null === $lastProject ? null : strtotime($lastProject->posted);
        
        $doc = new DOMDocument();
        $doc2 = new DOMDocument();
        
        $pageLimit = 2;
        for ($page = 1; $page <= $pageLimit; $page++) {
            $content = file_get_contents('http://jobs.designcrowd.com/graphic-design-jobs.aspx?page=' . $page);
            
            @$doc->loadHTML($content);
			
            $xpath = new DOMXPath($doc);
			
			$items = $xpath->query("//*[@id='tabcompact']/div/table/tbody/tr");
			
            foreach ($items as $item) {
        
                $project = $this->ProjectListFields;				
				
                $titleElement = $xpath->query('td[2]/a', $item)->item(0);
                $project['title'] = trim($titleElement->nodeValue);
                //$project['external_url'] = $titleElement->attributes->item(1)->nodeValue;
                $project['external_url'] = 'http://jobs.designcrowd.com' . $xpath->query('td[2]/a', $item)->item(0)->getAttribute('href');
				
				$project['external_id'] = str_replace('http://jobs.designcrowd.com/job.aspx?id=', '', $project['external_url']);
                $project['category_id'] = 4;
                
                $projectContent = file_get_contents($project['external_url']);
                $projectContent = mb_convert_encoding($projectContent, 'utf-8', mb_detect_encoding($projectContent));
                $projectContent = mb_convert_encoding($projectContent, 'html-entities', 'utf-8');
                @$doc2->loadHTML($projectContent);
                $xpath2 = new DOMXPath($doc2);
                
                $projectDescription = $xpath2->query('//span[@id="ctl00_mainContent_briefSummary_lblTask"]');
                if (!$projectDescription->length) {
                    continue;
                }
                $project['description'] = trim($projectDescription->item(0)->nodeValue);
                $project['title'] = $xpath2->query('//a[@id="ctl00_mainContent_lnkBriefTitle"]')->item(0)->nodeValue;
                
                $deadline = $xpath2->query('//span[@id="ctl00_mainContent_briefSummary_lblDeadline"]')->item(0)->nodeValue;
                $project['ends'] = date('Y-m-d H:i:s', strtotime($deadline));
                $project['posted'] = date('Y-m-d H:i:s');
                
                $price = trim($xpath2->query('//div[@id="ctl00_mainContent_panelToolsPayments"]/div/table/tr/td[2]/div')->item(0)->nodeValue);
                if (preg_match('|^([^\d]+)(\d+)$|umis', $price, $matches)) {
                    $project['budget_currency'] = array_search($matches[1], $currencies) ?: 1;
                    $project['budget_low'] = $project['budget_high'] = $matches[2];
                }
				
                $projectModel->importProject($project, Application_Model_DbTable_Platforms::DESIGNCROWD_ID);
                sleep(2);
            }
        }
    }
    
    public function from99designAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
        $projectModel = new Application_Model_DbTable_Projects();
        $platformCategoryModel = new Application_Model_DbTable_PlatformCategories();
        
        $currencies = array(1=>'$', 2=>'€', 3=>'£');
        
        $lastProject = $projectModel->getLast(Application_Model_DbTable_Platforms::NNDESIGN_ID);
        $lastProjectDate = null === $lastProject ? null : strtotime($lastProject->posted);
        
        
        $doc2 = new DOMDocument();
        
        $rss = Zend_Feed::import('http://99designs.com/feed');
        
        foreach ($rss as $item) {
            $project = $this->ProjectListFields;
            
            $project['title'] = $item->title();
            $project['external_url'] = $item->id();
            if (!preg_match('|-(\d+)$|umis', $project['external_url'], $matches)) {
                continue;
            }
            $project['external_id'] = $matches[1];
            $project['category_id'] = 4;
            $project['posted'] = date('Y-m-d H:i:s', strtotime($item->updated()));
            $project['description'] = strip_tags($item->summary());
            
            $projectContent = file_get_contents($project['external_url'] . '/brief');
            if (!$projectContent) {
                continue;
            }
            $projectContent = mb_convert_encoding($projectContent, 'utf-8', mb_detect_encoding($projectContent));
            $projectContent = mb_convert_encoding($projectContent, 'html-entities', 'utf-8');
            @$doc2->loadHTML($projectContent);
            $xpath2 = new DOMXPath($doc2);
            
            $project['bids'] = (int)$xpath2->query('//li[@id="contest-tabs-designers-tab"]/a/span[@class="info"]/span')->item(0)->nodeValue;
            
            $files = array();
            $projectDescription = $xpath2->query('//dl[@id="contest-brief-attributes"]/dt | //dl[@id="contest-brief-attributes"]/dd');
            if ($projectDescription->length) {
                $description = '';
                $descrKey = '';
                foreach ($projectDescription as $key => $descr) {
                    if ($descr->attributes->item(0)->nodeValue == 'brief-attachments brief-attribute-value') {
                        $attachments = $xpath2->query('//ul[@id="attachments-list"]/li/h3/a');
                        if ($attachments->length) {
                            foreach($attachments as $file) {
                                $files[] = array(
                                    'file_name' => $file->nodeValue,
                                    'file_url' => $file->attributes->item(0)->nodeValue
                                );
                            }
                        }
                    } else {
                        if ($key % 2) {
                            $descrValue = preg_replace("/\n\s+/", "\n", $descr->nodeValue);
                            $description .= "\n\n" . $descrKey . "\n" . trim($descrValue);
                        } else {
                            $descrKey = trim($descr->nodeValue);
                        }
                    }
                }
                $project['description'] = trim($description);
            }
            
            $price = trim($xpath2->query('//div[@id="contest-info-amount"]/div/span/span')->item(0)->nodeValue);
            if (preg_match('|^([^\d\s]+)\s*(\d+)$|umis', $price, $matches)) {
                $project['budget_currency'] = array_search($matches[1], $currencies) ?: 1;
                $project['budget_low'] = $project['budget_high'] = $matches[2];
            }
            $projectModel->importProject($project, Application_Model_DbTable_Platforms::NNDESIGN_ID, $files);
            sleep(2);
        }
    }
    
    public function fromcrowdspringAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
        $projectModel = new Application_Model_DbTable_Projects();
        
        $currencies = array(1=>'$', 2=>'€', 3=>'£');
        
        $lastProject = $projectModel->getLast(Application_Model_DbTable_Platforms::CROWDSPRING_ID);
        $lastProjectDate = null === $lastProject ? null : strtotime($lastProject->posted);
        
        $doc = new DOMDocument();
        $doc2 = new DOMDocument();
        
        /*$http = new Zend_Http_Client();
        $loginPage = $http->setUri('http://www.crowdspring.com/login/');
        $loginPage->*/
        
        $pageLimit = 3;
        $terminate = false;
        for ($page = 1; $page <= $pageLimit; $page++) {
            $content = file_get_contents('http://www.crowdspring.com/browse/?page=' . $page);
            
            @$doc->loadHTML($content);
            $xpath = new DOMXPath($doc);
            
            $items = $xpath->query('body/div/div/table[@class="browse clearfix"]/tbody/tr[contains(@class, "result")]');
            foreach ($items as $item) {
                $project = $this->ProjectListFields;
                
                $project['external_url'] = 'http://www.crowdspring.com' . $xpath->query('td[@class="project"]/a', $item)->item(0)->attributes->item(1)->nodeValue;
                if (!preg_match('|/project/(\d+)_|umis', $project['external_url'], $matches)) {
                    continue;
                }
                $project['external_id'] = $matches[1];
                $project['category_id'] = 4;
                
                $projectContent = file_get_contents($project['external_url'] . 'details/');
                $projectContent = mb_convert_encoding($projectContent, 'utf-8', mb_detect_encoding($projectContent));
                $projectContent = mb_convert_encoding($projectContent, 'html-entities', 'utf-8');
                @$doc2->loadHTML($projectContent);
                $xpath2 = new DOMXPath($doc2);
                
                $project['title'] = $xpath2->query('body/div[@id="wrapper"]/div[@class="pageheader"]/h1/a')->item(0)->nodeValue;
                
                $projectDescription = $xpath2->query('//div[@class="brief"]/h3 | //div[@class="brief"]/p');
                
                $description = '';
                $descrKey = '';
                foreach ($projectDescription as $key => $descr) {
                    if ($key % 2) {
                        $descrValue = preg_replace("/\n\s+/", "\n", $descr->nodeValue);
                        $description .= "\n\n" . $descrKey . "\n" . trim($descrValue);
                    } else {
                        $descrKey = trim($descr->nodeValue);
                    }
                }
                $project['description'] = trim($description) ?: 'Log in to view';
                
                $dates = $xpath2->query('//dl[@class="dates"]/dd/p');
                $timestampPosted = strtotime(str_replace('Starts:', '', $dates->item(0)->nodeValue));
                $project['posted'] = date('Y-m-d H:i:s', $timestampPosted);
                $project['ends'] = date('Y-m-d H:i:s', strtotime(str_replace('Ends:', '', $dates->item(1)->nodeValue)));
                
                if (!$terminate && $timestampPosted <= $lastProjectDate) {
                    $terminate = true;
                    $pageLimit = $page;
                }
                
                $price = trim($xpath2->query('//li[@class="awards"]/strong')->item(0)->nodeValue);
                $project['budget_currency'] = 1;
                $project['budget_low'] = $project['budget_high'] = str_replace('$', '', $price);
                
                $projectModel->importProject($project, Application_Model_DbTable_Platforms::CROWDSPRING_ID);
                sleep(2);
            }
        }
    }
    
    public function fromflexjobsAction()
    {
        $categories = array(
            'web-design' => 4,
            'web-software-development-programming' => 1,
            'computer-it' => 1,
            'art-products-services' => 4,
            'account-management' => 8,
            'accounting' => 9,
            'advertising-pr' => 8,
            'bilingual' => 3,
            'business-development' => 9,
            'data-entry' => 5,
            'editing' => 3,
            'engineering' => 6,
            'entertainment-media' => 4,
            'entrepreneurial' => 9,
            'executive-mngmt' => 8,
            'internet-ecommerce' => 1,
            'legal' => 9,
            'manager' => 9,
            'marketing' => 8,
            'math-economics' => 6,
            'sales-business-development' => 8,
            'consumer-products' => 8,
            'pharmaceutical-science' => 6,
            'transcription' => 3,
            'writing-editing-journalism' => 3,
            'news-journalism' => 3
        );
        
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
        $projectModel = new Application_Model_DbTable_Projects();
        
        $currencies = array(1=>'$', 2=>'€', 3=>'£');
        
        $lastProject = $projectModel->getLast(Application_Model_DbTable_Platforms::FLEXJOBS_ID);
        $lastProjectDate = null === $lastProject ? null : strtotime($lastProject->posted);
        
        $doc = new DOMDocument();
        $doc2 = new DOMDocument();
        
        $pageLimit = 3;
        $terminate = false;
        for ($page = 1; $page <= $pageLimit; $page++) {
            $content = file_get_contents('http://www.flexjobs.com/jobs/new?page=' . $page);
            
            @$doc->loadHTML($content);
            $xpath = new DOMXPath($doc);
            
            $items = $xpath->query('//ul[@id="joblist"]/li[contains(@class, "row")]');
            foreach ($items as $item) {
                $project = $this->ProjectListFields;
                
                $titleElement = $xpath->query('ul/li/div[@class="job-title"]/a', $item);
                if (!$titleElement->length) {
                    continue;
                }
                $titleElement = $titleElement->item(0);
                $project['title'] = trim($titleElement->nodeValue);
                $project['external_url'] = 'http://www.flexjobs.com' . $titleElement->attributes->item(0)->nodeValue;
                if (!preg_match('|-(\d+)$|umis', $project['external_url'], $matches)) {
                    var_dump($matches);
                    continue;
                }
                $project['external_id'] = $matches[1];
                
                $projectContent = file_get_contents($project['external_url']);
                $projectContent = mb_convert_encoding($projectContent, 'utf-8', mb_detect_encoding($projectContent));
                $projectContent = mb_convert_encoding($projectContent, 'html-entities', 'utf-8');
                @$doc2->loadHTML($projectContent);
                $xpath2 = new DOMXPath($doc2);
                
                $project['description'] = trim(strip_tags($xpath2->query('//ul[@class="items"]/li[@class="bdots2"][1]/div[@class="col2"]/div/div/p')->item(0)->nodeValue));
                
                $posted = $xpath2->query('//ul[@class="items"]/li[@class="bdots2"][2]/div[@class="col2"]/ul/li[1]')->item(0)->nodeValue;
                $timestampPosted = strtotime(str_replace('Date Posted:', '', $posted));
                $project['posted'] = date('Y-m-d H:i:s', $timestampPosted);
                
                $project['category_id'] = 10;
                $category = $xpath2->query('//ul[@class="items"]/li[@class="bdots2"][2]/div[@class="col2"]/ul/li[2]/a[1]')->item(0)->attributes->item(0)->nodeValue;
                if (preg_match('|/([^/]+)$|umis', $category, $matches)) {
                    if (isset($categories[$matches[1]])) {
                        $project['category_id'] = $categories[$matches[1]];
                    }
                }
                
                if (!$terminate && $timestampPosted <= $lastProjectDate) {
                    $terminate = true;
                    $pageLimit = $page;
                }
                
                $projectModel->importProject($project, Application_Model_DbTable_Platforms::FLEXJOBS_ID);
                sleep(2);
            }
        }
    }
    
    public function rssAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        require_once('HTMLParser/simple_html_dom.php');
        /*---For GetACoder----------------*/
        $GAC_JobTypes = array('Project'=>2, 'Hourly Job'=>1,'Question'=>3);
        $GAC_to_replace = array('$',' ','&nbsp;',',');
        $GAC_replace_with = array('','','','');
        $modelFromCurl = new Application_Model_Curl();
        /*------------------------------------------*/
        
        $request = $this->getRequest();
        $platformId = (int)$request->getParam('plid');
        $projectModel = new Application_Model_DbTable_Projects();
        $platformCategoryModel = new Application_Model_DbTable_PlatformCategories();
        
        $RSS_Urls = array(
            Application_Model_DbTable_Platforms::IFREELANCE_ID => 'http://www.ifreelance.com/feeds/rss/projects.aspx?v=2.0',
            Application_Model_DbTable_Platforms::GETACODER_ID => 'http://www.getacoder.com/projects/rss.xml',
            Application_Model_DbTable_Platforms::GURU_ID => 'http://www.guru.com/pro/ProjectResults.aspx?BID=0&LOC=2',
            Application_Model_DbTable_Platforms::FREELANCESWITCH_ID => 'http://feeds.feedburner.com/FSAllJobs'
        );
        
        if (array_key_exists($platformId, $RSS_Urls)) {
            $doc1 = new DOMDocument();
            $doc1->load($RSS_Urls[$platformId]);
            $xpath = new DOMXpath($doc1);
            
            $projectItems = $xpath->query("channel/item");
            foreach ($projectItems as $item) {
                $project = $this->ProjectListFields;
                $files = array();
                
                $project['title'] = $xpath->query("title", $item)->item(0)->nodeValue;
                $project['description'] = $xpath->query("description", $item)->item(0)->nodeValue;
                $project['posted'] = date('Y-m-d H:i:s', strtotime($xpath->query("pubDate", $item)->item(0)->nodeValue));
                $project['external_url'] = $xpath->query("link", $item)->item(0)->nodeValue;
                $project['guid'] = $xpath->query("guid", $item)->item(0)->nodeValue;
                
                if (isset($xpath->query("category", $item)->item(0)->nodeValue)) {
                    $project['category'] = $xpath->query("category", $item)->item(0)->nodeValue;
                }
                
                switch ($platformId) {
                    case Application_Model_DbTable_Platforms::IFREELANCE_ID:
                        $job_html = file_get_html($project['external_url']);
                        
                        $project_specs = $job_html->find('div.project-specs', 0)->find('div.section', 0);
                        $project['ends'] = date('Y-m-d H:i:s', strtotime($project_specs->find('div.right-column', 0)->plaintext));
                        $project['posted'] = date('Y-m-d H:i:s', strtotime($project_specs->find('div.right-column', 2)->plaintext));
                        $project['bids'] = (int)$project_specs->find('div.right-column', 4)->plaintext;
                        
                        $price = str_replace(',', '', trim($job_html->find('div.project-details', 0)->find('div.right-column', 0)->plaintext));
                        if (preg_match('|^Less than \$(\d+)$|ui', $price, $matches)) {
                            $project['budget_high'] = $matches[1];
                        } elseif (preg_match('|^Between \$(\d+) and \$(\d+)$|ui', $price, $matches)) {
                            $project['budget_low'] = $matches[1];
                            $project['budget_high'] = $matches[2];
                        } elseif (preg_match('|^Over \$(\d+)$|ui', $price, $matches)) {
                            $project['budget_low'] = $matches[1];
                        }
                        
                        $categoryUrl = $job_html->find('div#ctl00_mainBlockContentPlaceHolder_ctTrail_ctTrail', 0)
                            ->find('a', 2)->href;
                        if ($categoryUrl && preg_match('|^/find/projects/browse\.aspx\?c=(\d+)|ui', $categoryUrl, $matches)) {
                            $project['category_id'] = $platformCategoryModel->getCategoryId(Application_Model_DbTable_Platforms::IFREELANCE_ID, $matches[1]);
                        }
                        
                        parse_str(parse_url($project['external_url'], PHP_URL_QUERY), $getParams);
                        $project['external_id'] = $getParams['projectid'];
                        break;
                        
                    case Application_Model_DbTable_Platforms::GETACODER_ID:
                        $project_id = rtrim(substr($project['external_url'], strrpos($project['external_url'], '_') + 1, strlen($project['external_url'])), '.html');
                        
                        if (is_numeric($project_id)) {
                            $project['external_id'] = $project_id;
                        }
                        
                        $external_url = str_replace(' ', '%20', $project['external_url']);
                        
                        $CurlPageHtml = $this->getCurlContent($external_url);
                        $pos_start = strpos($CurlPageHtml, '<table cellSpacing="0" cellPadding="2" border="0">');
                        $pos_end = strpos($CurlPageHtml, '</table>', $pos_start);
                        
                        $table_info = substr($CurlPageHtml, $pos_start, $pos_end - $pos_start) . '</table>';
                        
                        $chat_msg_start = strpos($CurlPageHtml, '../pmb/chatmsg.php?id=', $pos_end);
                        $chat_msg_end =  strpos($CurlPageHtml, '#view', $chat_msg_start);
                        $user_msg_url = substr($CurlPageHtml, $chat_msg_start, $chat_msg_end - $chat_msg_start);
                        parse_str(parse_url($user_msg_url, PHP_URL_QUERY), $_MY_GET);
                        if (!isset($_MY_GET['to'])) {
                            continue 2;
                        }
                        $project['external_user_id'] = (int)$_MY_GET['to'];
                        
                        $pos_start = mb_strpos($CurlPageHtml, "<span id ='descr1'>", 0, 'utf-8');
                        
                        $pos_end = mb_strpos($CurlPageHtml, '<form name=orderForm>', $pos_start, 'utf-8');
                        
                        $desc_info = mb_substr($CurlPageHtml, $pos_start, $pos_end - $pos_start, 'utf-8' ) ;
                        
                        $desc_html =  str_get_html($desc_info); 
                        $description = $desc_html->find('#descr1', 0);
                        $description->find('br', 0)->outertext = '';
                        $description->find('br', 1)->outertext = '';
                        $description->find('font',0)->outertext = '';
                        $description->find('hr',0)->outertext = '';
                        
                        $gac_description = $description->innertext;
                        
                        // we do not truncate descriptions anymore
                        /*$max_gac_desc_len = 1000;
                        if (mb_strlen($gac_description, 'utf-8') > $max_gac_desc_len) {
                            $gac_description = mb_substr($gac_description, 0, $max_gac_desc_len - 3, 'utf-8');
                            $gac_description .= '...';
                        }*/
                        
                        $project['description'] = trim(strip_tags(str_replace('<br>', "\n", $gac_description)));
                        
                        // remove non-unicode characters
                        $regex = <<<'END'
/
  (
    (?: [\x00-\x7F]                 # single-byte sequences   0xxxxxxx
    |   [\xC0-\xDF][\x80-\xBF]      # double-byte sequences   110xxxxx 10xxxxxx
    |   [\xE0-\xEF][\x80-\xBF]{2}   # triple-byte sequences   1110xxxx 10xxxxxx * 2
    |   [\xF0-\xF7][\x80-\xBF]{3}   # quadruple-byte sequence 11110xxx 10xxxxxx * 3 
    ){1,100}                        # ...one or more times
  )
| .                                 # anything else
/x
END;
                        $project['description'] = preg_replace($regex, '$1', $project['description']);
                        
                        $table_with_job_type = str_get_html($table_info);
                        
                        $JobType = $table_with_job_type->find('tr', 0)->find('td', 1)->find('small', 0)->innertext;
                        if (isset($GAC_JobTypes[$JobType])) {
                            $project['jobtype'] = $GAC_JobTypes[$JobType];
                            if ($project['jobtype'] == 2) {
                                $budget_txt = $table_with_job_type->find('tr', 1)->find('td', 1)->find('small', 0)->innertext;
                                $budget_txt = str_replace($GAC_to_replace, $GAC_replace_with, $budget_txt);
                                $budget_info = explode('-', $budget_txt);
                                $project['budget_low'] = (float)$budget_info[0];
                                $project['budget_high'] = (float)$budget_info[1];
                            }
                        }
                        $tr_attach_nr = $project['jobtype'] == 1 ? 5 : 3;
                        
                        if (preg_match('|Skills:</b></font></td>\s*<td><small><a href="http://www.getacoder.com/projects/[^_]+_(\d+).htm"|umis', $CurlPageHtml, $matches)) {
                            $project['category_id'] = $platformCategoryModel->getCategoryId(Application_Model_DbTable_Platforms::GETACODER_ID, $matches[1]);
                        }
                        
                        $td_attach = $table_with_job_type->find('tr', $tr_attach_nr)->find('td', 1);
                        
                        foreach ($td_attach->find('a') as $a) {
                            $files[] = array(
                                'file_name' => $a->innertext,
                                'file_url' => str_replace('../', 'http://www.getacoder.com/', $a->getAttribute('href'))
                            );
                        }
                        
                        $pos_start_bid = strpos($CurlPageHtml, '<table border="0" cellpadding="0" bgcolor="White" cellspacing="0" style="border-collapse: collapse" bordercolor="#111111" width="100%" id="AutoNumber16">');
                        if ($pos_start_bid !== false) {
                            $pos_1_end_bid = strpos($CurlPageHtml, '</table>', $pos_start_bid); 
                            $pos_2_end_bid = strpos($CurlPageHtml, '</table>', $pos_1_end_bid + 8);
                            $table_info_bid = substr($CurlPageHtml, $pos_start_bid, $pos_2_end_bid - $pos_start_bid ) . '</table>';
                            
                            $bid_html = str_get_html($table_info_bid);
                            preg_match('!\d+!', $bid_html->find('tr', 0)->find('td.atd', 0)->find('a', 0)->find('nobr', 0)->innertext, $NrOfBids);
                            
                            $avg_bid_txt = $bid_html->find('td', 7)->find('table', 0)->find('tr', 0)->find('td', 2)->plaintext;
                            $avg_bid = str_replace($GAC_to_replace, $GAC_replace_with, $avg_bid_txt);
                            
                            $project['bids'] = (int)$NrOfBids[0];
                            $project['bids_avg'] = (float)$avg_bid;
                        }
                        break;
                        
                    case Application_Model_DbTable_Platforms::FREELANCESWITCH_ID:
                        $job_html = file_get_html($project['external_url']);
                        $buget_html =  $job_html->find('div#page', 0)
                            ->find('div.page_inner_wrap', 0)
                            ->find('div.job_ad', 0)
                            ->find('div.job_details', 0)
                            ->find('h5', 0)->plaintext;
                        $buget_html = str_replace(',', '', $buget_html);
                        $my_arr = explode(' to ', $buget_html);
                        
                        $project['description'] = trim(strip_tags(str_replace(array('<br>', '&nbsp;'), array("\n", ' '), $project['description'])));
                        
                        preg_match('!\d+!', $my_arr[0], $budget_low);
                        $project['budget_low'] = (float)$budget_low[0];
                        $project['budget_high'] = $project['budget_low'];
                        if (isset($my_arr[1])) {
                            preg_match('!\d+!', $my_arr[1], $budget_high);
                            $project['budget_high'] = (float)$budget_high[0];
                        }
                        
                        $projectId = substr($project['external_url'], strrpos($project['external_url'], '/') + 1, strlen($project['external_url']));
                        $project['external_id'] = $projectId;
                        
                        $categoryUrl = $job_html->find('div.breadcrumbs', 0)->find('a', 1)->href;
                        if ($categoryUrl) {
                            $categoryId = str_replace('http://jobs.freelanceswitch.com/categories/', '', $categoryUrl);
                            $project['category_id'] = $platformCategoryModel->getCategoryId(Application_Model_DbTable_Platforms::FREELANCESWITCH_ID, $categoryId);
                        }
                        
                        break;
                        
                    case Application_Model_DbTable_Platforms::GURU_ID:
                        $cookieFile = dirname(__FILE__).'/cookies/44cookie.txt';
                        $options = array(
                            CURLOPT_USERAGENT => 'Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.1)',
                            CURLOPT_COOKIEFILE => $cookieFile,
                            CURLOPT_COOKIEJAR => $cookieFile,
                            CURLOPT_HEADER => true,
                            CURLOPT_RETURNTRANSFER => true,
                            CURLOPT_POST => false,
                            CURLOPT_SSL_VERIFYPEER => FALSE,
                            CURLOPT_URL => $project['external_url']
                        );
                        $cUrlHtml = $modelFromCurl->CurlPlatformPostClassic($options);
                       // $job_html = str_get_html($cUrlHtml);
                        
						//Date 19/02/2014
						$job_html= file_get_html($project['external_url']);
						
                        $budgetElement = $job_html->find('div#mainLeft', 0)
                            ->find('ul#ctl00_guB_ucProjectDetail_ulBudget', 0);
                        $project['bids'] = (int)$budgetElement->find('li#liTotalProposal', 0)
                            ->find('span#snpProposalCount', 0)->plaintext;
                        $project['description'] = trim($job_html->find('div#mainLeft', 0)
                            ->find('#descriptionSec', 0)
                            ->find('.projDescription', 0)->plaintext);
                        $price = $budgetElement->innertext;
                        if (preg_match('#<li>\s*<span>([^<]+)</span>\s*(Fixed Price|Hourly Rate)\s*</li>#imsu', $price, $matches)) {
                            $project['jobtype'] = $matches[2] == 'Hourly Rate' ? 1 : 2;
                            $price = trim($matches[1]);
                            if (preg_match('#^(Under|max) \$([k\d]+)$#iu', $price, $matches)) {
                                $project['budget_high'] = str_replace('k', '000', $matches[2]);
                            } elseif (preg_match('#^\$([k\d]+)\s*-\s*\$([k\d]+)$#iu', $price, $matches)) {
                                $project['budget_low'] = str_replace('k', '000', $matches[1]);
                                $project['budget_high'] = str_replace('k', '000', $matches[2]);
                            } elseif (preg_match('#^(Over|min) \$([k\d]+)$#iu', $price, $matches)) {
                                $project['budget_low'] = str_replace('k', '000', $matches[2]);
                            }
                        }
                        $categoryUrl = $job_html->find('div#ctl00_guB_ucProjectDetail_dvTitleSec', 0) 
                            ->find('a', 0)->getAttribute('href');
                        if ($categoryUrl) {
                            $categoryId = str_replace('/pro/search.aspx?cid=', '', $categoryUrl);
                            $project['category_id'] = $platformCategoryModel->getCategoryId(Application_Model_DbTable_Platforms::GURU_ID, $categoryId);
                        }
                        
                        $projectId = substr($project['external_url'], strrpos($project['external_url'], '/') + 1, strlen($project['external_url']));
                        $project['external_id'] = $projectId;
                        break;
                }
              // echo "<pre>";
				//print_r($project);
                $projectModel->importProject($project, $platformId, $files);
            }
			//die;
        }
    }
    
    public function getCurlContent($curl_url)
    {
        $cookieFile = dirname(__FILE__).'/cookies/44cookie.txt';
        $Curl_Obj = curl_init();
        
        if (!file_exists( $cookieFile)) {
            $fh = fopen($cookieFile, "w");
            fwrite($fh, '');
            fclose($fh);
            chmod($cookieFile, 0777);
        }
        
        curl_setopt ($Curl_Obj, CURLOPT_COOKIEFILE, $cookieFile); 
        curl_setopt ($Curl_Obj, CURLOPT_COOKIEJAR, $cookieFile); 
        
        $userAgent = 'Mozilla/5.0 (Windows NT 6.1; rv:17.0) Gecko/20100101 Firefox/17.0';
        curl_setopt($Curl_Obj, CURLOPT_USERAGENT, $userAgent);
        curl_setopt ($Curl_Obj, CURLOPT_HEADER, 0);
        curl_setopt($Curl_Obj, CURLOPT_AUTOREFERER, TRUE);
        curl_setopt ($Curl_Obj, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($Curl_Obj, CURLOPT_VERBOSE, 1);
        curl_setopt($Curl_Obj, CURLOPT_POST, 0);
        curl_setopt ($Curl_Obj, CURLOPT_URL, $curl_url);
        $response = curl_exec ($Curl_Obj);
        return $response;
    }
	public function frommonsterAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $projectModel = new Application_Model_DbTable_Projects();
        $platformCategoryModel = new Application_Model_DbTable_PlatformCategories();
        $currencies = array(1=>'$', 2=>'€', 3=>'£');       
        $lastProject = $projectModel->getLast(Application_Model_DbTable_Platforms::MONSTER_ID);
        $lastProjectDate = null === $lastProject ? null : strtotime($lastProject->posted);
        $rss = Zend_Feed_Reader::import('http://rss.jobsearch.monster.com/rssquery.ashx?q=Web%20Developer');
		foreach($rss as $entry) {
		$project = $this->ProjectListFields;
		$project['title'] = $entry->getTitle();
        $project['external_url'] = $entry->getLink();
        $project['external_id'] = $entry->getId();
        $project['category_id'] = 1;
        $project['posted'] = date('Y-m-d H:i:s', strtotime($entry->getDateModified()));
        $project['description'] = strip_tags($entry->getContent());
		$project['budget_currency'] =1;
		if(strtotime($entry->getDateModified()) > $lastProjectDate) {
		$files = array();
            $projectModel->importProject($project, Application_Model_DbTable_Platforms::MONSTER_ID, $files);
            sleep(2);
			}
        }
    }
	public function fromcoroflotAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $projectModel = new Application_Model_DbTable_Projects();
        $platformCategoryModel = new Application_Model_DbTable_PlatformCategories();
        $currencies = array(1=>'$', 2=>'€', 3=>'£');       
        $lastProject = $projectModel->getLast(Application_Model_DbTable_Platforms::COROFLOT_ID);
        $lastProjectDate = null === $lastProject ? null : strtotime($lastProject->posted);
        $rss = Zend_Feed_Reader::import('http://feeds.feedburner.com/coroflot/AllJobs');   
		foreach($rss as $entry) {
		$project = $this->ProjectListFields;
		$project['title'] = $entry->getTitle();
        $project['external_url'] = $entry->getLink();
        $project['external_id'] = $entry->getId();
        $project['category_id'] = 4;
        $project['posted'] = date('Y-m-d H:i:s', strtotime($entry->getDateModified()));
       // $project['description'] = strip_tags($entry->getContent());
		$project['budget_currency'] =1;
		$pieces = explode("/", $project['external_id']);
		$project['external_id'] =$pieces[4];
		
		$projectContent = file_get_contents($project['external_url']);
	//	preg_match('/<div class=\"c">(.*?)<\/div>/s', $projectContent, $temp); // get data out of the page
			 // spits out the 1st occurance of your data
			// print_r($temp);die;
			// $project['description'] = strip_tags(@$temp[0]);
			// if(empty($project['description']))
			 //{
			 //	continue;
			// }
		  //echo  $projectContent = file_get_contents($project['external_url']);die;
		   
            if (!$projectContent) {
                continue;
            }
			$doc2 = new DOMDocument();
            //$projectContent = mb_convert_encoding($projectContent, 'utf-8', mb_detect_encoding($projectContent));
            $projectContent = mb_convert_encoding($projectContent, 'html-entities', 'utf-8');
            @$doc2->loadHTML($projectContent);
            $xpath2 = new DOMXPath($doc2);
           // $xpath->query("//div[@class='jobDetailsInfo']")
           $elements = $xpath2->query("//div[@class='c']/article/p");
		   if (!is_null($elements)) {
		    $c = 0;
   foreach ($elements as $element) {
    if($c==0)
      $nodes = $element->childNodes;
	 
	 $description= '';
      foreach ($nodes as $node) {
	  	
          $description = $description.$node->nodeValue;
		
     }
	  $c++;
   }
 }
  	//echo $description;die;
	   $project['description'] = $description;

		if(strtotime($entry->getDateModified()) > $lastProjectDate) {
		$files = array();
            $projectModel->importProject($project, Application_Model_DbTable_Platforms::COROFLOT_ID, $files);
            sleep(2);
			}
        }
    }
	public function frombehanceAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $projectModel = new Application_Model_DbTable_Projects();
        $platformCategoryModel = new Application_Model_DbTable_PlatformCategories();
        $currencies = array(1=>'$', 2=>'€', 3=>'£');
        $lastProject = $projectModel->getLast(Application_Model_DbTable_Platforms::BEHANCE_ID);
        $lastProjectDate = null === $lastProject ? null : strtotime($lastProject->posted);
        $rss = Zend_Feed_Reader::import('http://www.behance.net/feeds/projects');
		foreach($rss as $entry) {
		$project = $this->ProjectListFields;
		$project['title'] = $entry->getTitle();
        $project['external_url'] = $entry->getLink();
        $project['external_id'] = $entry->getId();
        $project['category_id'] = 4;
        $project['posted'] = date('Y-m-d H:i:s', strtotime($entry->getDateModified()));
        $project['description'] = strip_tags($entry->getContent());
		$project['budget_currency'] =1;
		$pieces = explode("/", $project['external_id']);
		//print_r($pieces);die;
		$project['external_id'] =$pieces[5];
		/*
		   $projectContent = file_get_contents($project['external_url']);
            if (!$projectContent) {
                continue;
            }
			$doc2 = new DOMDocument();
            $projectContent = mb_convert_encoding($projectContent, 'utf-8', mb_detect_encoding($projectContent));
            $projectContent = mb_convert_encoding($projectContent, 'html-entities', 'utf-8');
            @$doc2->loadHTML($projectContent);
            $xpath2 = new DOMXPath($doc2);
           // $xpath->query("//div[@class='jobDetailsInfo']")
           $project['description'] = $xpath2->query("//article[@class='col539']p");
		*/
		//echo "<pre>";
		//print_r($project);die;
		if(strtotime($entry->getDateModified()) > $lastProjectDate) {
		$files = array();
            $projectModel->importProject($project, Application_Model_DbTable_Platforms::BEHANCE_ID, $files);
            sleep(2);
			}
        }
    }
	public function fromdiceAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $projectModel = new Application_Model_DbTable_Projects();
        $platformCategoryModel = new Application_Model_DbTable_PlatformCategories();
        $currencies = array(1=>'$', 2=>'€', 3=>'£');       
        $lastProject = $projectModel->getLast(Application_Model_DbTable_Platforms::DICE_ID);
        $lastProjectDate = null === $lastProject ? null : strtotime($lastProject->posted);
        $rss = Zend_Feed_Reader::import('http://www.dice.com/job/rss');   
		//echo $rss;
		foreach($rss as $entry) {
		$project = $this->ProjectListFields;
		$project['title'] = $entry->getTitle();
        $project['external_url'] = $entry->getLink();
        $project['external_id'] = $entry->getId();
        $project['category_id'] = 1;
        $project['posted'] = date('Y-m-d H:i:s');
       // $project['description'] = strip_tags($entry->getContent());
		$project['budget_currency'] =1;
		//$pieces = explode("/", $project['external_id']);
		//print_r($pieces);
		//$project['external_id'] =$pieces[5];
			
		    $projectContent = file_get_contents($project['external_url']);
			preg_match('#\<div id="detailDescription"\>(.+?)\<\/div\>#s', $projectContent, $temp); // get data out of the page
			 // spits out the 1st occurance of your data
			 $project['description'] = strip_tags(@$temp[0]);
			 if(empty($project['description']))
			 {
			 	continue;
			 }
		   	
			
		//if(strtotime($entry->getDateModified()) > $lastProjectDate) {
		$files = array();
            $projectModel->importProject($project, Application_Model_DbTable_Platforms::DICE_ID, $files);
            sleep(2);
			//}
        }
    }
	
	public function fromkropAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $projectModel = new Application_Model_DbTable_Projects();
        $platformCategoryModel = new Application_Model_DbTable_PlatformCategories();
        $currencies = array(1=>'$', 2=>'€', 3=>'£');       
        $lastProject = $projectModel->getLast(Application_Model_DbTable_Platforms::KROP_ID);
        $lastProjectDate = null === $lastProject ? null : strtotime($lastProject->posted);
        $rss = Zend_Feed_Reader::import('http://www.krop.com/services/feeds/rss/latest/');   
		//echo $rss;
		foreach($rss as $entry) {
		$project = $this->ProjectListFields;
		$project['title'] = $entry->getTitle();
        $project['external_url'] = $entry->getLink();
        $project['external_id'] = $entry->getId();
        $project['category_id'] = 4;
        $project['posted'] = date('Y-m-d H:i:s', strtotime($entry->getDateModified()));
        $project['description'] = strip_tags($entry->getContent());
		$project['budget_currency'] =1;
		//$pieces = explode("/", $project['external_id']);
		//print_r($pieces);
		//$project['external_id'] =$pieces[5];
		/*
		   $projectContent = file_get_contents($project['external_url']);
            if (!$projectContent) {
                continue;
            }
			$doc2 = new DOMDocument();
            $projectContent = mb_convert_encoding($projectContent, 'utf-8', mb_detect_encoding($projectContent));
            $projectContent = mb_convert_encoding($projectContent, 'html-entities', 'utf-8');
            @$doc2->loadHTML($projectContent);
            $xpath2 = new DOMXPath($doc2);
           // $xpath->query("//div[@class='jobDetailsInfo']")
           $project['description'] = $xpath2->query("//article[@class='col539']p");
		*/
		//echo "<pre>";
		//print_r($project);die;
		if(strtotime($entry->getDateModified()) > $lastProjectDate) {
		$files = array();
            $projectModel->importProject($project, Application_Model_DbTable_Platforms::KROP_ID, $files);
            sleep(2);
			}
        }
    }
	public function fromcraigslistAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $projectModel = new Application_Model_DbTable_Projects();
        $platformCategoryModel = new Application_Model_DbTable_PlatformCategories();
        $currencies = array(1=>'$', 2=>'€', 3=>'£');       
        $lastProject = $projectModel->getLast(Application_Model_DbTable_Platforms::CRAIGSLIST_ID);
        $lastProjectDate = null === $lastProject ? null : strtotime($lastProject->posted);
        $rss = Zend_Feed_Reader::import('http://losangeles.craigslist.org/web/index.rss');   
		//echo $rss;
		foreach($rss as $entry) {
		$project = $this->ProjectListFields;
		$project['title'] = $entry->getTitle();
        $project['external_url'] = $entry->getLink();
        $project['external_id'] = $entry->getId();
        $project['category_id'] = 1;
        $project['posted'] = date('Y-m-d H:i:s', strtotime($entry->getDateModified()));
        $project['description'] = strip_tags($entry->getContent());
		$project['budget_currency'] =1;
		//$pieces = explode("/", $project['external_id']);
		//print_r($pieces);
		//$project['external_id'] =$pieces[5];
		/*
		   $projectContent = file_get_contents($project['external_url']);
            if (!$projectContent) {
                continue;
            }
			$doc2 = new DOMDocument();
            $projectContent = mb_convert_encoding($projectContent, 'utf-8', mb_detect_encoding($projectContent));
            $projectContent = mb_convert_encoding($projectContent, 'html-entities', 'utf-8');
            @$doc2->loadHTML($projectContent);
            $xpath2 = new DOMXPath($doc2);
           // $xpath->query("//div[@class='jobDetailsInfo']")
           $project['description'] = $xpath2->query("//article[@class='col539']p");
		*/
		//echo "<pre>";
		//print_r($project);die;
		if(strtotime($entry->getDateModified()) > $lastProjectDate) {
		$files = array();
            $projectModel->importProject($project, Application_Model_DbTable_Platforms::CRAIGSLIST_ID, $files);
            sleep(2);
			}
        }
    }
    public function fromsologigAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $projectModel = new Application_Model_DbTable_Projects();
        $platformCategoryModel = new Application_Model_DbTable_PlatformCategories();
        $currencies = array(1=>'$', 2=>'€', 3=>'£');       
        $lastProject = $projectModel->getLast(Application_Model_DbTable_Platforms::CRAIGSLIST_ID);
        $lastProjectDate = null === $lastProject ? null : strtotime($lastProject->posted);
        $rss = Zend_Feed_Reader::import('http://www.sologig.com/jobs');   
		//echo $rss;
		foreach($rss as $entry) {
		$project = $this->ProjectListFields;
		$project['title'] = $entry->getTitle();
        $project['external_url'] = $entry->getLink();
        $project['external_id'] = $entry->getId();
        $project['category_id'] = 1;
        $project['posted'] = date('Y-m-d H:i:s', strtotime($entry->getDateModified()));
        $project['description'] = strip_tags($entry->getContent());
		$project['budget_currency'] =1;
		//$pieces = explode("/", $project['external_id']);
		//print_r($pieces);
		//$project['external_id'] =$pieces[5];
		/*
		   $projectContent = file_get_contents($project['external_url']);
            if (!$projectContent) {
                continue;
            }
			$doc2 = new DOMDocument();
            $projectContent = mb_convert_encoding($projectContent, 'utf-8', mb_detect_encoding($projectContent));
            $projectContent = mb_convert_encoding($projectContent, 'html-entities', 'utf-8');
            @$doc2->loadHTML($projectContent);
            $xpath2 = new DOMXPath($doc2);
           // $xpath->query("//div[@class='jobDetailsInfo']")
           $project['description'] = $xpath2->query("//article[@class='col539']p");
		*/
		echo "<pre>";
		print_r($project);die;
		if(strtotime($entry->getDateModified()) > $lastProjectDate) {
		$files = array();
            $projectModel->importProject($project, Application_Model_DbTable_Platforms::CRAIGSLIST_ID, $files);
            sleep(2);
			}
        }
    }
		
    public function fromsimplyhiredAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $projectModel = new Application_Model_DbTable_Projects();
        $platformCategoryModel = new Application_Model_DbTable_PlatformCategories();
        $currencies = array(1=>'$', 2=>'€', 3=>'£');       
        $lastProject = $projectModel->getLast(Application_Model_DbTable_Platforms::SIMPLYHIRED_ID);
        $lastProjectDate = null === $lastProject ? null : strtotime($lastProject->posted);
        $rss = Zend_Feed_Reader::import('http://www.simplyhired.com/a/job-feed/rss/q-freelance');   
		//echo $rss;
		foreach($rss as $entry) {
		$project = $this->ProjectListFields;
		$project['title'] = $entry->getTitle();
        $project['external_url'] = $entry->getLink();
        $project['external_id'] = $entry->getId();
        $project['category_id'] = 1;
        $project['posted'] = date('Y-m-d H:i:s', strtotime($entry->getDateModified()));
        $project['description'] = strip_tags($entry->getContent());
		$project['budget_currency'] =1;
		//$pieces = explode("/", $project['external_id']);
		//print_r($pieces);
		//$project['external_id'] =$pieces[5];
		/*
		   $projectContent = file_get_contents($project['external_url']);
            if (!$projectContent) {
                continue;
            }
			$doc2 = new DOMDocument();
            $projectContent = mb_convert_encoding($projectContent, 'utf-8', mb_detect_encoding($projectContent));
            $projectContent = mb_convert_encoding($projectContent, 'html-entities', 'utf-8');
            @$doc2->loadHTML($projectContent);
            $xpath2 = new DOMXPath($doc2);
           // $xpath->query("//div[@class='jobDetailsInfo']")
           $project['description'] = $xpath2->query("//article[@class='col539']p");
		*/
		//echo "<pre>";
		//print_r($project);die;
		if(strtotime($entry->getDateModified()) > $lastProjectDate) {
		$files = array();
            $projectModel->importProject($project, Application_Model_DbTable_Platforms::SIMPLYHIRED_ID, $files);
            sleep(2);
			}
        }
    }
	public function sendsubscribemailAction()
	{
		
		$projectTable = new Application_Model_DbTable_Projects();
		$subCatTable = new Application_Model_DbTable_Freelancerscategories();
		$subUserRow = $subCatTable->getAll();
		foreach($subUserRow as $userRow)
		{
		$jobs = '';
		$email = $userRow['email'];
		$config = array('auth' => 'login',
		'username' => 'support@searchfreelancejobs.com',
		'password' => '123qwe');
		$transport = new Zend_Mail_Transport_Smtp('mail.searchfreelancejobs.com', $config);							
        $mail = new Zend_Mail();
        $mail->setFrom('no-reply@SearchFreelanceJobs.com', 'SearchFreelanceJobs.com');
        $mail->setSubject('Matching jobs');
		 //$mail->addTo();
		//echo "<pre>";
		//($userRow['email']);
		$projectRow = $projectTable->getUserSubscribeProject(array('title','external_url','id'),$userRow['account_id'],'5');
		$category = $subCatTable->getUserSubscribeCategory($userRow['account_id']);
		$url_parameters='';
		if(!empty($category))
		{
				$url_parameters .='/c/';
				foreach($category as $cat)
				{
					$url_parameters .= $cat['category_id'].'x';
				}
				$url_parameters = rtrim($url_parameters,'x');
		}
				
		if(!empty($projectRow))
		{								
		foreach($projectRow as $project)
		{
			 $url = 'http://searchfreelancejobs.com/projects/index' . $url_parameters.'/pid/'.$project['id'];
        	$jobs .= '<br>'.$project['title'].'<br> '."<a href='".$url."'>Apply Job</a><br></br>";
		}
		$jobs .= "<br><br><a href='http://searchfreelancejobs.com/profile/unsubscribe'>Unsubscribe</a><br><br>";
		$mail->setBodyHtml("<br>$jobs<br>With love,<br>The SearchFreelanceJobs.com Team");
        $mail->addTo($email);						
        $mail->send($transport);
		
	}	
			
		}
		echo "Mail sent";
		die;				
						
	}
	
	/* function used to check the expiry date of a freelance upgrade account */
	public function checkupgradeAction()
	{
		$transationTable = new  Application_Model_DbTable_Transactions();
		$no = $transationTable->checkExpiry();
		echo $no;
		die;
	}
    
	public function fromrssfreelancerAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $projectModel = new Application_Model_DbTable_Projects();
        $platformCategoryModel = new Application_Model_DbTable_PlatformCategories();
        $currencies = array(1=>'$', 2=>'€', 3=>'£');       
        $lastProject = $projectModel->getLast(Application_Model_DbTable_Platforms::DICE_ID);
        $lastProjectDate = null === $lastProject ? null : strtotime($lastProject->posted);
        //$rss = Zend_Feed_Reader::import('http://www.freelancer.com/rss.xml');   
			$client = new Zend_Http_Client('http://www.freelancer.com/rss.xml');
			$client->setParameterPost(array(
			'param1' => 'value'
			));
			$response = $client->request('POST');
			echo $response->getBody();
		echo $rss;die;
		foreach($rss as $entry) {
		$project = $this->ProjectListFields;
		$project['title'] = $entry->getTitle();
        $project['external_url'] = $entry->getLink();
        $project['external_id'] = $entry->getId();
        $project['category_id'] = 1;
        $project['posted'] = date('Y-m-d H:i:s');
        $project['description'] = strip_tags($entry->getContent());
		$project['budget_currency'] =1;
		//$pieces = explode("/", $project['external_id']);
		//print_r($pieces);
		//$project['external_id'] =$pieces[5];
		/*
		   $projectContent = file_get_contents($project['external_url']);
            if (!$projectContent) {
                continue;
            }
			$doc2 = new DOMDocument();
            $projectContent = mb_convert_encoding($projectContent, 'utf-8', mb_detect_encoding($projectContent));
            $projectContent = mb_convert_encoding($projectContent, 'html-entities', 'utf-8');
            @$doc2->loadHTML($projectContent);
            $xpath2 = new DOMXPath($doc2);
           // $xpath->query("//div[@class='jobDetailsInfo']")
           $project['description'] = $xpath2->query("//article[@class='col539']p");
		*/
		//echo "<pre>";
		//print_r($project);die;
		//if(strtotime($entry->getDateModified()) > $lastProjectDate) {
		$files = array();
            $projectModel->importProject($project, Application_Model_DbTable_Platforms::DICE_ID, $files);
            sleep(2);
			//}
        }
    }
   
    
    
}
