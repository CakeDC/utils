 <?php  

App::uses('Model', 'Model');
class Account extends Model { 
    var $name = 'Account'; 
	
	var $actsAs = array(
		'Utils.StateMachine' => array (
			'initial' => 'registered',
			'column' => 'state',
			'states' => array(
				'registered' => array('exit' => 'exitTest'),
				'opened' => array('after'=>'afterTest', 'enter'=>'enterTest'),
				'frozen' => array('after'=>'afterTest'),
				'closed' => array('enter'=>'enterTest'),
			),
			
			'events' => array(
				'confirm' => array(
					'transitions' => array (
						'to'=>'opened',
						'from'=>array('registered'),
					),
				),
				'close' => array(
					'transitions' => array (
						'to'=>'closed',
						'from'=>array('frozen','opened'),
						'guard'=>'guardFn',
					),
				),
				'froze' => array(
					'transitions' => array (
						'to'=>'frozen',
						'from'=>array('opened'),
					),
				),
				'open' => array(
					'transitions' => array (
						'to'=>'opened',
						'from'=>array('frozen'),
					),
				),
			)
		),
	);
	

	public $tiggers = array();
	
    public function clearTriggers() {
		$this->tiggers = array('enter'=>null,'after'=>null,'exit'=>null);
	}
	
    public function enterTest($state) {
		$this->tiggers['enter'] = $state->getName();
		return true;
	}

    public function afterTest($state) {
		$this->tiggers['after'] = $state->getName();
		return true;
	}
	
    public function exitTest($state) {
		$this->tiggers['exit'] = $state->getName();
		return true;
	}

	/**
		test of guard callback.
		Does not allow close event for Alex.
	*/
    public function guardFn($state) {
		$data=$this->data[$this->name];
		if ($data['name']=='Alex') return false;
		return true;
	}
	
}

class StateMachineTestCase extends CakeTestCase { 
	public $fixtures = array('plugin.Utils.account'); 

/**
 * @var Account
 */
    public $Account;

    public function testAlex() {
        $this->Account =ClassRegistry::init('Account');
		$this->Account->create(array('name'=>'Alex'));
		$this->Account->save();
		$data = $this->Account->findByName('Alex');
        $id = $data['Account']['id'];
		$this->assertEqual('registered',$data['Account']['state']);
		
		$this->Account->set($data);
		$this->Account->setStateConfirm();
		$this->assertEqual($this->Account->tiggers['enter'],'opened');
		$this->assertEqual($this->Account->tiggers['exit'],'registered');
		$this->assertEqual($this->Account->tiggers['after'],'opened');
		$this->Account->clearTriggers();

		$data=$this->Account->findByName('Alex');
		$this->Account->set($data);
		$this->assertEqual('opened',$data['Account']['state']);
		$this->assertTrue($this->Account->isStateOpened());
		$this->assertFalse($this->Account->isStateRegistered());

		$this->Account->setStateFroze();
		$this->assertNull($this->Account->tiggers['exit']);
		$this->assertEqual($this->Account->tiggers['after'], 'frozen');
		$this->assertNull($this->Account->tiggers['enter']);
		$this->Account->clearTriggers();
		$data=$this->Account->findByName('Alex');
		$this->Account->set($data);
		$this->assertEqual('frozen',$data['Account']['state']);
		$this->assertTrue($this->Account->isStateFrozen());
		$this->assertFalse($this->Account->isStateOpened());
		
		$this->Account->setStateClose();

		//guard test
		$this->Account->clearTriggers();
		$data=$this->Account->findByName('Alex');
		$this->assertEqual('frozen',$data['Account']['state']);
		$this->assertTrue($this->Account->isStateFrozen());
		$this->assertFalse($this->Account->isStateClosed());
	}

    public function testFred() { 
        $this->Account = ClassRegistry::init('Account');
		$this->Account->create(array('name'=>'Fred'));
		$this->Account->save();
		$this->Account->set($this->Account->findByName('Fred'));
		$this->Account->clearTriggers();
		$this->Account->setStateConfirm();
		$this->Account->set($this->Account->findByName('Fred'));
		$this->Account->clearTriggers();
		$this->Account->setStateClose();
		$this->assertNull($this->Account->tiggers['exit']);
		$this->assertNull($this->Account->tiggers['after']);
		$this->assertEqual($this->Account->tiggers['enter'],'closed');
		$account = $this->Account->findByName('Fred');
		$this->Account->set($account);
		$this->assertEqual('closed',$account['Account']['state']);
		$this->assertTrue($this->Account->isStateClosed());
		$this->assertFalse($this->Account->isStateFrozen());		
	}

} 
