<?php

namespace Sms;

class CheckIPs
{
	//access environment variable (restrict ips)
	public function __construct()
	{

		$this->ips = getenv('RESTRICT_IPS');
	}

	//filter and move the restrict ips into array
	public function getIpsArray()
	{

		if ($this->ips) {
			$ips = explode(',', trim($this->ips));
			
			foreach ($ips as $ip) {
				$allowedIps []= $ip;
			}

			return $allowedIps;
		}
	}
}
