<?php 
namespace Jack;
use Exception;

class Sms
{
	private $username;
	private $password;

	public function login($set)
	{
		$this->username = $set['username'];
		$this->password = $set['password'];
	}
	protected function loginCheck()
	{
		if (empty($this->username)) throw new Exception("請設定帳號");
		if (empty($this->password)) throw new Exception("請設定密碼");
	}
	public function send($phone, $name, $msg)
	{
		try {
			$this->loginCheck();
			if (!preg_match("/^09[0-9]{8}$/", $phone)) {
				return array(
					'msgid' => '',
					'statuscode' => 'v',
					'statusmsg' => '手機格式錯誤',
					'AccountPoint' => ''
				);
			}
			$g = array(
				'username' => $this->username,
				'password' => $this->password,
				'dstaddr' => $phone,
				'destname' => mb_convert_encoding($name,"big5","utf-8"),
				'dlvtime' => '',
				'vldtime' => '',
				'smbody' => mb_convert_encoding($msg,"big5","utf-8"),
				'response' => urlencode($this->callback)
			);
			$url = 'http://smsapi.mitake.com.tw/api/mtk/SmSend?'.http_build_query($g);
			$rs = file_get_contents($url);
			return $this->rs_decode($rs);
		} catch(Exception $e) {
			echo $e->getMessage();
		}
	}
	public function info($msgid)
	{
		try {
			$this->loginCheck();
			$g = array(
				'username' => $this->username,
				'password' => $this->password,
				'msgid' => $msgid,
			);
			$url = 'http://smsapi.mitake.com.tw/api/mtk/SmQuery?'.http_build_query($g);
			$rs = file_get_contents($url);
			$r = explode("\r\n", $rs);
			$data = array();
			foreach ($r as $key => $value) {
				if (empty($value)) continue;
				$re = explode('	', $value);
				$data[] = array(
					'msgid' => $re[0],
					'status' => $re[1],
					'time' => $re[2],
					'msg' => $this->status_code($re[1]),
				);
			}
			return $data;
		} catch(Exception $e) {
			echo $e->getMessage();
		}
	}
	private function rs_decode($rs)
	{
		$receiveDataArray = explode("\r\n", $rs);
		$msgidArray = explode("=", $receiveDataArray[1]);
		$statuscodeArray = explode("=", $receiveDataArray[2]);
		$accountPointArray = explode("=", $receiveDataArray[3]);
		$statuscode = $statuscodeArray[1];			//傳送結果狀態代碼
		$accountPoint = $accountPointArray[1];		//帳號目前所剩點數
		return array(
			'msgid' => $msgidArray[1],
			'statuscode' => $statuscodeArray[1],
			'statusmsg' => $this->status_code($statuscodeArray[1]),
			'AccountPoint' => $accountPointArray[1]
		);
	}
	private function status_code($code)
	{
		$status_msg = array(
			'*' => '系統發⽣錯誤，請聯絡三⽵資訊窗⼝⼈員',
			'a' => '簡訊發送功能暫時停⽌服務，請稍候再試',
			'b' => '簡訊發送功能暫時停⽌服務，請稍候再試',
			'c' => '請輸入帳號',
			'd' => '請輸入密碼',
			'e' => '帳號、密碼錯誤',
			'f' => '帳號已過期',
			'h' => '帳號已被停⽤',
			'l' => '帳號已達到同時連線數上限',
			'm' => '必須變更密碼，在變更密碼前，無法使⽤簡訊發送服務',
			'n' => '密碼已逾期，在變更密碼前，將無法使⽤簡訊發送服務',
			'p' => '沒有權限使⽤外部Http程式',
			'r' => '系統暫停服務，請稍後再試',
			's' => '帳務處理失敗，無法發送簡訊',
			't' => '簡訊已過期',
			'u' => '簡訊內容不得為空⽩',
			'v' => '無效的⼿機號碼',
			'w' => '查詢筆數超過上限',
			'x' => '發送檔案過⼤，無法發送簡訊',
			'y' => '參數錯誤',
			'z' => '查無資料',
			'0' => '預約傳送中',
			'1' => '已送達業者',
			'2' => '已送達業者',
			'4' => '已送達⼿機',
			'5' => '內容有錯誤',
			'6' => '⾨號有錯誤',
			'7' => '簡訊已停⽤',
			'8' => '逾時無送達',
			'9' => '預約已取消'
		);
		return $status_msg[$code];
	}
}