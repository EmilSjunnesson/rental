<?php 
class CDiceLogic {
  protected $queryGET = array();
  protected $dices = array();
  protected $sum = 0;
  protected $totalSum = 0;
  protected $ses;
  protected $rounds = 0;
  
  public function __construct(array $queryGET) {
    $this->queryGET = $queryGET;
    // Creates new session object if it does not already exist
    if(isset($_SESSION['sessionObj'])) {
      $this->ses = $_SESSION['sessionObj'];
    } else {
      $this->ses = new CDiceSession();
      $_SESSION['sessionObj'] = $this->ses;
    }
  }
  
  // Gets variabels from the session object
  private function GetSession() {
  	if($this->ses->GetTotalSum() < 100) {
      $this->dices = $this->ses->GetDiceArray();
      $this->rounds = $this->ses->GetRounds();
      $this->sum = $this->ses->GetSum();
      $this->totalSum = $this->ses->GetTotalSum();
  	} else {
  	  $this->Restart();
  	}
  }
  
  // Rolls a dice 
  protected function Roll() {
    $this->GetSession();      
    $dice = new CDice();
    $dice->Roll();
    $roll = $dice->GetRollScore();
    // If the outcome is one reset the round
    if($roll === 1) {
      $this->dices = array(1);
      $this->sum = 0;
      $this->rounds++;
      $this->ses->SetDiceArray($this->dices);
      $this->ses->SetSum($this->sum); 
      $this->ses->SetRounds($this->rounds);
      return 'Kastade: ' . $roll . ', omgången är slut och alla osparade poäng går förlorade. Kasta igen';      
    // Else add the outcome to the rounds score
    } else {
      $this->dices[] = $roll;
      $this->sum += $roll;
      $this->ses->SetDiceArray($this->dices);
      $this->ses->SetSum($this->sum);    
      return 'Kastade: ' . $roll;
    }
  }
  
  // Saves score and resets round
  protected function Store() {
    $this->GetSession();
    if($this->sum <= 0) {
      return 'Det finns inga poäng att spara';      
    }
    $this->totalSum += $this->sum;
    $this->sum = 0;
    $this->dices = array();
    $this->rounds++;
    $this->ses->SetTotalSum($this->totalSum);
    $this->ses->SetSum($this->sum);
    $this->ses->SetDiceArray($this->dices);
    $this->ses->SetRounds($this->rounds);
    // Checks if th saved score is equal or over 100, if it is redirecting to win page
    if($this->totalSum >= 100) {
      header('Location: ?win', true, 302);
      exit;
    }
    return 'Omgången avslutad, sparar poäng. Kasta för att fortsätta samla in mer';      
  }
  
  // Resets the game (i.e. the variables
  protected function Restart() {
    $this->GetSession();
    $this->dices = array();
    $this->rounds = 0;
    $this->sum = 0;
    $this->totalSum = 0;
    $this->ses->SetDiceArray($this->dices);
    $this->ses->SetRounds($this->rounds);
    $this->ses->SetSum($this->sum);
    $this->ses->SetTotalSum($this->totalSum);      
  }
  
  // Selects function based on querystring
  protected function Action() {     
    if(isset($this->queryGET['roll'])) {
      return "<output class='info'>" . $this->Roll() . "</output>";      
    } else if(isset($this->queryGET['store'])) {
      return "<output class='info'>" . $this->Store() . "</output>";      
    } else if(isset($this->queryGET['restart'])) {
      return $this->Restart();      
    }
  }
}
