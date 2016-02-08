<?php 
class CDiceDisplay extends CDiceLogic {
	
  private $db;	
  
  //Gets the pages querystring and passes it to the parent constructor
  public function __construct(array $queryGET, $db) {
    parent::__construct($queryGET);
    $this->db = $db;
  }
  
  // Returns html code to represent dice array
  private function GetRollsAsImageList() {
    $html = "<ul class='dice'>";
    if(count($this->dices) > 0) {
      foreach($this->dices as $val) {
        $html .= "<li class='dice-{$val}'></li>";
      }
    }
    $html .= "</ul>";
    return $html;        
  }
  
  // Prints out all html including gameboard and controls
  public function PrintHTML() {
  	$movies = CMovie::threeLatest($this->db, 70, 105);
    if(isset($this->queryGET['win'])) { 
      $html = "Grattis! Du lyckades samla ihop 100 poäng, du behövde bara {$this->ses->GetRounds()} omgångar.<br>"
      . ($this->ses->GetRounds() <= 10 ? "<div class='price'>
      <p style='text-align: left;'>GRATTIS! Du har nu chans att vinna gratis uthyrning av dessa tre filmer i en månad.</p>
      {$movies}
      </div>" : "Du var tvyärr inte tillräckligt skicklig för att få en chans att hyra filmer gratis. Det är bara till att försöka igen!") .
      "<br><br><a class='as-button' href='?restart'>Starta nytt spel</a>";      
    } else {
      $html = "<p>Tärningsspelet 100 är ett enkelt, men roligt, tärningsspel. Det gäller att samla ihop poäng för att komma till 100. I varje omgång kastar du tärning tills du väljer att stanna och spara poängen eller tills det dyker upp en 1:a. Om du slår en 1:a förlorar du alla poäng som samlats in i rundan.</p>
      <hr><br>
      <div class='price'>
      <p style='text-align: left;'>Om du lyckas komma upp till 100 poäng under 10 omgångar har du chans att få hyra nedanstående tre filmer gratis i en hel månad!</p>
      {$movies}
      </div>
      {$this->Action()}<br><br>
      <div style='height: 40px;'>
      {$this->GetRollsAsImageList()}</div><br>
      Poäng i nuvarande omgång: {$this->sum}<br> 
      Sparade poäng: {$this->totalSum}<br>
      Antal omgångar: {$this->rounds}<br><br>
      <a class='as-button' href='?roll'>Kasta</a> <a class='as-button' href='?store'>Spara poäng</a> <a class='as-button' href='?restart'>Återställ spel</a>";
    }
    return $html;
  }
}
