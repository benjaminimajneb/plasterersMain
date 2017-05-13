
var currentColour;

var height = $('#screen').height();
var width = $('#screen').width();
var paper = Raphael('screen', width, height);

var circleY = width - height/12;
var colour;
var colours = [];
for (var i=1; i<=6; i++) {

	//generate random colour

	//calc height and draw circle.
	circleX = i*height/12;
	colours[i] = paper.circle(circleY, circleX, height/12).attr({'fill':colour});
	colours[i].click(changeColour.bind(false,colour));
}
function changeColour(){
	//change mouse hover circle
	
	//change mouse on-click to fill correct colour. - store globally in var currentColour?

}

// right fifth of screen given over to six colours

// left four fifths of screen given over to random circles
maybe draw random circles, then trace over all paths from the points of intersection, then 
detect position of click from ispointinside each individual path and then colour the circle by 
checking if that point is inside each circle (given an array of the original circle objects)

or randomly generate points, then join sets of three points by curves.  
can ensure no crossovers or anything by always keeping a record of every drawn path even if randomly generated.
eg draw three points. draw three curves between. draw extra point to right of triangle. draw new path of three curves, 
ONE of which is the same as the existing curve between the two shared points.
