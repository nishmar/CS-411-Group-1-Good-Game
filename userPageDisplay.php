<?php
// Start the session
session_start();
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

<!-- Temporary style for table -->
<!DOCTYPE html>
<html>
<head>
    <style>
        table, th, td {
            border: 1px solid black;
        }
    </style>
</head>
<body>

<?php

// Get user (from session) $_SESSSION[" "]
// Get profile owner from url (user must have clicked on a user profile)

//temp variables representing user and profile owner

$user = $_SESSION["userID"];
$profileOwner = $_GET["profileOwner"];

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

//Display games from userGame list matching user, separated by status
function displayGameList($user, $profileOwner, $conn){
    echo "<br> Currently Playing:";
    displayGames($user, $profileOwner,'playing', $conn);

    echo "<br> Completed:";
    displayGames($user, $profileOwner,'completed', $conn);

    echo "<br> Want to Play:";
    displayGames($user, $profileOwner,'plan', $conn);

    echo "<br> Dropped:";
    displayGames($user, $profileOwner,'dropped', $conn);

    echo "<br> Reviews:";
    displayReviews($profileOwner, $conn);
}

/*
 * Displays section of profile page grouped by status
 */
function displayGames($user, $profileOwner, $status, $conn){
    $sql = "SELECT * FROM `gamecache`.`usergames` WHERE `User ID` LIKE '$profileOwner' AND `Status` LIKE '$status'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0){
        echo "<table><tr><th>Title</th><th>Rating</th><th></th><th></th></tr>";
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
                editButton($gameID);
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
 * Edit button
 */
function editButton($gameID){
echo "<form action='gameListForm.php'>
           <input type='hidden' name='gameID' value='$gameID'>
           <input type='submit' name='listChange' value='Edit'>
           </form>";
}

/*
 * Delete button
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
            echo "<br>" .$gameTitle;
            echo "<br> " .date('Y/m/d', $timestamp);
            echo "<br>" .$row["Rating"];
            echo "<br>".$review;
        }
    }
}

function displayFriendList($user, $profileOwner, $conn){
    echo "<br>";
    $sql = "SELECT * FROM `gamecache`.`userfriends` WHERE `User ID` LIKE '$profileOwner'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0){

        echo "<br> Friends: ";

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

//Main:
$conn = connectSQL();
displayGameList($user, $profileOwner, $conn);
displayFriendList($user, $profileOwner, $conn);

$conn->close();

?>
