<?php
// Start the session
session_start();
include 'navbar.php';

?>

<!--
* userPageDisplay.php
*
* Displays a user game list. Verifies if user is list owner to enable edits and deletes.
*
* Created by Nishi
* Date: 11/22/14
*/
-->

<?php
function connectSQL(){
//MySQL default credentials
    $servername = "localhost";
    $username = "root";
    $password = "";

// Create connection
    $conn = new mysqli($servername, $username, $password);

// Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    //echo "Connected successfully";

    return $conn;
}
//Validate inputs
function test_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

//Display games from userGame list matching user, separated by status
function displayGameList($user, $profileOwner, $conn){

    echo "<br> <center><h3>Currently Playing:</h3></center>";
    displayGames($user, $profileOwner,'playing', $conn);

    echo "<br> <center><h3>Completed:</h3></center>";
    displayGames($user, $profileOwner,'completed', $conn);

    echo "<br> <h3><center>Want to Play:</center></h3>";
    displayGames($user, $profileOwner,'plan', $conn);

    echo "<br> <h3><center>Dropped:</center></h3>";
    displayGames($user, $profileOwner,'dropped', $conn);

    echo "<br> <h3><center>Reviews:</center></h3>";

    echo "<center>";
    displayReviews($profileOwner, $conn);
    echo "</center>";

}

/*
 * Displays games under given status
 */
function displayGames($user, $profileOwner, $status, $conn){
    $sql = "SELECT * FROM `gamecache`.`usergames` WHERE `User ID` LIKE '$profileOwner' AND `Status` LIKE '$status'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0){
echo"

<table class='table table-striped'>
            <tr><th>Title</th><th>Rating</th><th></th><th></th></tr>";
        while ($row = $result->fetch_assoc()) {
            $gameID = $row["Game ID"];

            //Search game cache for game ID and retrieve additional information
            $gameInfo = $conn->query("SELECT name FROM `gamecache`.`gamelist` WHERE `ID` LIKE '$gameID'");
            $gameTitle = $gameInfo->fetch_assoc()["name"];


            //Display results in table
            echo "<tr><td>" .$gameTitle ."</td><td>" .$row["Rating"] ."</td>";

            //If user is owner of list, give edit and delete options
            if (isOwner($user, $profileOwner)) {
                echo "<td>";
                editButton($gameID, $gameTitle);
                echo "</td><td>";
                deleteButton($gameID);
                echo "</td></tr>";
            }
            else{
                echo "</tr>";
            }
        }
        echo "</table>";
    }
}

/*
 * Edit game button
 */
function editButton($gameID, $gameTitle){
    echo "<form action='gameListForm.php'>
        <input type='hidden' name='title' value='$gameTitle'>
           <input type='hidden' name='gameID' value='$gameID'>
           <input type='submit' name='listChange' value='Edit'>
           </form>";
}

/*
 * Delete game button
 */
function deleteButton($gameID){
    echo "<form action='updateGameList.php'>
            <input type='hidden' name='gameID' value='$gameID'>
           <input type='submit' name='listChange' value='Delete'>
           </form>";
}

function deleteFriend($friendID){
    echo "<form action='updateFriendList.php'>
            <input type='hidden' name='friendID' value='$friendID'>
           <input type='submit' name='listChange' value='Delete'>
           </form>";
}

/*
 *  Check if user is owner of profile
 */
function isOwner($user, $profileOwner){
    if ($user==$profileOwner) {
        return true;
    }
    else{
        return false;
    }
}

/*
 *  Displays reviews from most recent to oldest
 */
function displayReviews($profileOwner, $conn){
    $sql = "SELECT * FROM `gamecache`.`usergames` WHERE `User ID` LIKE '$profileOwner' AND `Review` IS NOT NULL AND `Review`!='' ORDER BY `Timestamp` DESC";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {

            $gameID = $row["Game ID"];
            $gameInfo = $conn->query("SELECT name FROM `gamecache`.`gamelist` WHERE `ID` LIKE '$gameID'");
            $gameTitle = $gameInfo->fetch_assoc()["name"];
            $timestamp = $row["Timestamp"];
            $review = $row["Review"];

            echo "<br>";
            echo "<br><h4>" .$gameTitle. "</h4>";
            echo "<br> " .date('Y/m/d', $timestamp);
            echo "<br>" .$row["Rating"];
            echo "<br>".$review;
        }
    }
}

/*
 * Displays users profile owner is following
 */
function displayFriendList($user, $profileOwner, $conn){
    echo "<br>";
    $sql = "SELECT * FROM `gamecache`.`userfriends` WHERE `User ID` LIKE '$profileOwner'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0){

        echo "<br> <h3>Following:</h3> ";

        while ($row = $result->fetch_assoc()) {
            $friendID = $row["Friend ID"];

            echo "<br>";
            echo " <a href='userPageDisplay.php?profileOwner=$friendID'> $friendID </a> ";

            //If user is owner of list, give edit and delete options
            if (isOwner($user, $profileOwner)) {
                deleteFriend($friendID);
            }
        }
    }
}

/*
 * Displays list of users that have added the profile owner to friends list.
 * No add or delete options regardless of user: users may follow whoever they like.
 */
function displayFollowerList($profileOwner, $conn){
    echo "<br>";
    $sql = "SELECT * FROM `gamecache`.`userfriends` WHERE `Friend ID` LIKE '$profileOwner'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0){

        echo "<br> <h3>Followers: </h3>";

        while ($row = $result->fetch_assoc()) {
            $followerID = $row["User ID"];

            echo "<br>";
            echo " <a href='userPageDisplay.php?profileOwner=$followerID'> $followerID </a> ";

            }
    }
}
?>



<?php

//Get user viewing profile from session
$user = $_SESSION["userID"];

//Retrieve profile owner from link
$profileOwner = test_input($_GET["profileOwner"]);

$conn = connectSQL();

echo "<div class='container padding-top'>
    <div class='row'>
        <div class='col-sm-3'>";
        displayFriendList($user, $profileOwner, $conn);
displayFollowerList($profileOwner, $conn);

echo "</div>";

echo"<div class='col-sm-9'>";

displayGameList($user, $profileOwner, $conn);
echo "</div></div>";


$conn->close();

?>
