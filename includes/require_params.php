<?php

/**
* 
*/
class reqParams {
	public static function reqLogin(){
		return  array('email','password');
	}
	public static function reqUomInsert(){
		return array('uom_id','uom_desc');
	}
}