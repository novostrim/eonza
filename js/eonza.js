/*
    Eonza 
    (c) 2015 Novostrim, OOO. http://www.eonza.org
    License: MIT
*/

Eonza = function() {
	this.website = 'http://www.eonza.org/';
	// Link to How to restore the password
	this.resetPass = this.website + 'how-to-reset-password.html';
    // The custom text of the footer
    this.footer = '';
    
    this.testProp = "Hello World";
}

Eonza.prototype.foo = function() {
  console.log(this.testProp);
}

Eonza.prototype.bar = function() {
  console.log(this.testProp);
}

var enz = new Eonza();

