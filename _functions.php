<?php
function getSvshopStatus()
{
	$args->date = date("Ymd000000", time()-60*60*24);
	$today = date("Ymd");

	// Member Status
	$oMemberAdminModel = &getAdminModel('member');
	$status->member->todayCount = $oMemberAdminModel->getMemberCountByDate($today);
	$status->member->totalCount = $oMemberAdminModel->getMemberCountByDate();

	// Document Status
	$oDocumentAdminModel = &getAdminModel('document');
	$statusList = array('PUBLIC', 'SECRET');
	$status->document->todayCount = $oDocumentAdminModel->getDocumentCountByDate($today, array(), $statusList);
	$status->document->totalCount = $oDocumentAdminModel->getDocumentCountByDate('', array(), $statusList);

	// Comment Status
	$oCommentModel = &getModel('comment');
	$status->comment->todayCount = $oCommentModel->getCommentCountByDate($today);
	$status->comment->totalCount = $oCommentModel->getCommentCountByDate();

	// shoppping-mall
	$oNstoreAdminModel = &getAdminModel('nstore');
	if($oNstoreAdminModel)
	{
		$salesInfoToday = $oNstoreAdminModel->getSalesInfo($today);
		$salesInfoTotal = $oNstoreAdminModel->getSalesInfo();
		$status->nstore->todayCount = $salesInfoToday->count;
		$status->nstore->todayAmount = $salesInfoToday->amount;
		$status->nstore->totalCount = $salesInfoTotal->count;
		$status->nstore->totalAmount = $salesInfoTotal->amount;
		$status->nstore->orderStatus = $oNstoreAdminModel->getTotalStatus();
	}

	// contents-mall
	$oNstore_digitalAdminModel = &getAdminModel('nstore_digital');
	if($oNstore_digitalAdminModel)
	{
		$salesInfoToday = $oNstore_digitalAdminModel->getSalesInfo($today);
		$salesInfoTotal = $oNstore_digitalAdminModel->getSalesInfo();
		$status->nstore_digital->todayCount = $salesInfoToday->count;
		$status->nstore_digital->todayAmount = $salesInfoToday->amount;
		$status->nstore_digital->totalCount = $salesInfoTotal->count;
		$status->nstore_digital->totalAmount = $salesInfoTotal->amount;
		$status->nstore_digital->orderStatus = $oNstore_digitalAdminModel->getTotalStatus();
	}

	// for layer
	$oScmsAdminModel = &getAdminModel('scms');
	if($oScmsAdminModel)
	{
		$status->player->currentPlayCount = $oScmsAdminModel->getCurrentPlayCount();
	}

	return $status;
}

function getNewsFromSingleview()
{
	//Retrieve recent news and set them into context
	//$newest_news_url = sprintf("http://www.hq.com/?module=newsagency&act=getNewsagencyArticle&inst=notice&top=6&loc=%s", _XE_LOCATION_);
	//<zbxe_news released_version="" download_link=""><item url="http://www.hq.com/109931" date="">1�� ���ݰ�꼭 ���� ����</item><item url="http://www.hq.com/94224" date="">12�� ���ݰ�꼭 ���ึ�� �ȳ�</item><item url="http://www.hq.com/78654" date="">11�� ���ݰ�꼭 ���� ���� �ȳ�</item><item url="http://www.hq.com/66659" date="">10�� ���ݰ�꼭 ���� ����</item><item url="http://www.hq.com/44809" date="">9�� ���ݰ�꼭 ���� ����</item><item url="http://www.hq.com/39457" date="">8�� ���ݰ�꼭 ���ึ�� �ȳ�</item></zbxe_news>
	
	$newest_news_url = sprintf("http://singleview.co.kr/?module=newsagency&act=getNewsagencyArticle&inst=notice&top=6&loc=%s", _XE_LOCATION_);

	$cache_file = sprintf("%sfiles/cache/nstore_news.%s.cache.php", _XE_PATH_, _XE_LOCATION_);
	if(!file_exists($cache_file) || filemtime($cache_file)+ 60*60 < time())
	{
		// Considering if data cannot be retrieved due to network problem, modify filemtime to prevent trying to reload again when refreshing textmessageistration page
		// Ensure to access the textmessageistration page even though news cannot be displayed
		FileHandler::writeFile($cache_file,'');
		FileHandler::getRemoteFile($newest_news_url, $cache_file, null, 1, 'GET', 'text/html', array('REQUESTURL'=>getFullUrl('')));
	}

	if(file_exists($cache_file)) 
	{
		$oXml = new XeXmlParser();
		$buff = $oXml->parse(FileHandler::readFile($cache_file));

		$item = $buff->zbxe_news->item;
		if($item) 
		{
			if(!is_array($item)) 
			{
				$item = array($item);
			}

			foreach($item as $key => $val) {
				$obj = null;
				$obj->title = $val->body;
				$obj->date = $val->attrs->date;
				$obj->url = $val->attrs->url;
				$news[] = $obj;
			}
			return $news;
		}
	}
}
