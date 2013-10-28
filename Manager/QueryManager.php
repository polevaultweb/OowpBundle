<?php


namespace Outlandish\OowpBundle\Manager;


use Outlandish\OowpBundle\Helpers\OowpQuery;
use Outlandish\OowpBundle\Oowp;

class QueryManager extends \Outlandish\RoutemasterBundle\Manager\QueryManager {

	public function __construct(Oowp $postManager) {
		$this->postManager = $postManager;
	}

	public function query($queryArgs) {
		return new OowpQuery($queryArgs, $this->postManager, $this);
	}

} 