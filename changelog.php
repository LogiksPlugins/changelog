<?php
if(!defined('ROOT')) exit('No direct script access allowed');

class ChangeLog {
	public $cnt=1;
	public $root;
	public $writeLogToFile;
	public $sfrmt;
	private $logFileHandle;
	private $excludeDir;
	private $excludeFile;
	public function __construct($bdir,$frmt,$writeLog,$logFile,$excludeDir,$excludeFile) {
		$this->root=$bdir;
		$this->writeLogToFile=$writeLog;
		$this->sfrmt=$frmt;
		$this->excludeDir=$excludeDir;
		$this->excludeFile=$excludeFile;
		$this->logFileHandle=$logFile;
		if($writeLog && is_writable($this->logFileHandle)) {
			$this->logFileHandle=fopen($logFile,"w");
		} else {
			$this->logFileHandle=null;
		}
	}
	function __destruct() {
		if($this->logFileHandle!=null) fclose($this->logFileHandle);
	}
	public function dumpChangelog($dir,$frmDate,$toDate) {
		if(is_dir($dir)) {
			$fs=scandir($dir);
			foreach($fs as $a) {
				if($a=="." || $a=="..") continue;
				elseif(in_array($a,$this->excludeDir)) continue;
				$p=$dir."/".$a;
				$p=str_replace("//","/",$p);
				$this->dumpChangelog($p,$frmDate,$toDate);
			}
		} else {
			if(in_array(basename($dir),$this->excludeFile)) return;
			$dt=$this->getChange($dir,$frmDate,$toDate);
			if(strlen($dt)>0) {
				$path=str_replace($_SERVER["DOCUMENT_ROOT"],"",$dir);
				$path1=str_replace($this->root,"",$dir);
				printf($this->sfrmt,base64_encode($path),$this->cnt,$path,$path1,$dt);
				$this->cnt++;
				if($this->writeLogToFile) {
					$str =$dir.'=>'.$dt."\n";
					$this->writeLog($str);
				}
			}
		}
	}
	private function getChange($f,$frmDate,$toDate) {
		$modified = filemtime($f);
		$modified_str=date("d/m/Y H:i:s", $modified);
		if($frmDate < $modified && $modified<=$toDate)  {
			return $modified_str;
		} else {
			return "";
		}
	}
	private function writeLog($str) {
		if($this->logFileHandle!=null)
			fwrite($this->logFileHandle,$str);
	}
}