<?php

function writeFileToDatabase($filename,$dbTableName){
    
    $writeFilename = $filename;
  
    $getCurrentDate = new DateTime();
    $saveDateFormat = $getCurrentDate->format('Y-m-d H:i:s');
    
    DB::insert($dbTableName, array(
		"file_name"=> "$writeFilename",
		"insert_date"=> "$saveDateFormat",
		));
      
}
?>

<?php
function listFilesInDatabaseTable($dbTableName){
    
    $result = DB::select($dbTableName, array("file_id", "file_name", "insert_date") , " ORDER BY insert_date DESC");
?>

    <table style="margin-left:auto;margin-right:auto"  border="1">
        <tr>
            <td>Filename with location</td>
            <td>Timestamp</td>
            <td>Delete</td>
        </tr>
        
        <?php
           while ($row = DB::fetchRow($result)){
        ?>
          <tr>
            <td><?php echo $row->file_name;  ?> </td>
            <td><?php  echo $row->insert_date;   ?> </td>
              
                  <td>
                      <form class="SomeSpaceDude" name="delete-form" action="/"  method="post">
                          <input class="topcoat-button" type="hidden" value="<?php echo $row->file_id  ?>" name="delete" />
                          <input class="topcoat-button" type="submit" value="delete" /> 
                      </form>
                  </td>
        </tr>
        <?php
               
           }//close while for table row list 
        
        DB::freeResult($result);
    
        ?>
        <tr>
          <td colspan="3"></td>
        </tr>
    </table>
  
<?php
}//close listFilesInDatabaseTable function
?>

<?php

function deleteFromTable($removeThisRow,$dbTableName){
    
    $deleteThisRow = $removeThisRow;
    
    $result = DB::delete($dbTableName, "where file_id = $deleteThisRow");
    
}


//feel free to add more sauce
