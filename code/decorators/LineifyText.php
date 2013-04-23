<?php

class LineifyText extends Extension{
	
	function Lineify(){
		return str_replace("\n", "<br/>", $this->owner->getValue());
	}
	
}