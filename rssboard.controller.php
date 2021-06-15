

<?php
    /**
	 * @file	rssboard.controller.php
     * @class 	rssboardController
     * @author	ChoiHeeChul, KimJinHwan, ParkSunYoung
     * @brief 	rssboard Controller
     **/
require_once('./modules/rssboard/simplepie.php');
require_once('./modules/rssboard/lastrss.php');

class rssboardController extends rssboard {
	
	
    /**
     * @brief 초기화 / 게시글 작성을 위한 관리자 정보를 저장
    **/	
    function init() {
		$oMemberModel = &getModel('member');
		$this->admin_info = $oMemberModel->getMemberInfoByUserID('admin');		
    }
	
    /**
     * @brief rss 업데이트 대상 목록을 가져와서 각각 업데이트
     **/
    function doCrawl() {

		$output = executeQueryArray('rssboard.getRssboardAll');	
	
		// 실패시 처리
		if(!$output->toBool()) return $output;
		
		foreach ($output->data as $val) {
			$this->doUpdateRss($val);
		}
		
		return new baseobject(0,'success');
		}
		
		/**
		 * @brief 각 개별 RSS 를 업데이트
		 **/
		function doUpdateRss($rssboard)
		{
		if( !isset($rssboard) || !isset($rssboard->rssurl) )
			return ;
	
		// 최종 업데이트 기준일 가져오기
		$last_updatedate = 0;
		if( $rssboard->updatedate!=0 )
		{
			$last_updatedate = $this->getRegdateTime($rssboard->updatedate);
			
			// 최종 업데이트 시간이 10분 이내면 무시
		//	if( time() < ($last_updatedate + 0) )
		//		return ;
		}
		
		// document module의 controller 객체 생성
		$oDocumentController = &getController('document');	
		
		// 현재 시간을 업데이트 시간으로 설정
		$updatetime = date('YmdHis');
					
		// SimplePie Library 를 이용해 RSS 가져오기		

		$LRss = new lastRSS;

		$link = $rssboard->rssurl;
			
		$sRss = $LRss->get($link);


		
		// 최종 업데이트 일 이후에 작성된 글을 대상 게시판에 추가
		foreach(array_reverse($sRss['items'],1) as $item)
		{
			

			$date = new DateTime($item['pubDate']);
			$date->format('YmdHis');
			
			if ($last_updatedate > $date->format('YmdHis') )
			continue;

			$obj = null;
			
			
			// item link 를 가져오지 못할 경우 불가피하게 RSS 주소 사용
			if($item['link'])
			$link = $item['link'];
			

if(strpos($item['description'], "<![CDATA[") !== false) {  


     $d = $item['description'];
      $t = $item['title'];
       $l = $item['link'];

/*
     $d = iconv("EUC-KR","UTF-8", $item['description']); 
     $t = iconv("EUC-KR","UTF-8", $item['title']);
       $l =  iconv("EUC-KR","UTF-8", $item['link']);
*/

       $d= str_replace('<![CDATA[','', $d);
       $d= str_replace(']]>','', $d);

       $t= str_replace('<![CDATA[','', $t);
       $t= str_replace(']]>','', $t);

       $l= str_replace('<![CDATA[','', $l);
       $l= str_replace(']]>','', $l);



} else {  
     $d = $item['description'];
      $t = $item['title'];
       $l = $item['link'];
}  


			$obj->title = htmlspecialchars_decode($t);
			$obj->content =  htmlspecialchars_decode($d) . "<br/><br/><br/> 원문출처 : <a href='" . htmlspecialchars_decode($l) . "' target='_blank'>" . htmlspecialchars_decode($l) . "</a>";
			$obj->module_srl = $rssboard->module_srl;
			$obj->member_srl = $this->admin_info->member_srl;
			$obj->user_id =  $this->admin_info->user_id;
			$obj->user_name =  $this->admin_info->user_name;
			$obj->nick_name =  $this->admin_info->nick_name;
			$obj->email_address =  $this->admin_info->email_address;

			//$date = date("YmdHis");
			$obj->regdate = $this->getRegdateTime($date->format('YmdHis'));
			$obj->category_srl = $rssboard->category_srl;
			$obj->allow_comment = 'Y';
	
			$output=$oDocumentController->insertDocument($obj,true);
		}
		
		// 최종 업데이트 시간 저장
		$args = null;
		$args->updatetime = $updatetime;
		$args->rssboard_srl = $rssboard->rssboard_srl;
		$output = executeQuery('rssboard.updateRssboardDate',$args);
    }
	
    /**
    * @brief DB에 저장된 시간을 unixtimestamp 로 변환 /n
    * document.item.php 에서 차용
    */
    function getRegdateTime($regdate) {
        $year = substr($regdate,0,4);
        $month = substr($regdate,4,2);
        $day = substr($regdate,6,2);
        $hour = substr($regdate,8,2);
        $min = substr($regdate,10,2);
        $sec = substr($regdate,12,2);
        return mktime($hour,$min,$sec,$month,$day,$year);
    }
}

?>
