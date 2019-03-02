<?php
/**
 * Access Control AddOn
 * @author wolfgang[at]busch-dettum[dot]de Wolfgang Busch
 * @package redaxo5
 * @version März 2019
 */
#
class access_control_install {
#
public static function sql_action($sql,$query) {
   #   performing an SQL action using setQuery()
   #   including error message if fails
   #   $sql               SQL handle
   #   $query             SQL action
   #
   try {
        $sql->setQuery($query);
        $error='';
         } catch(rex_sql_exception $e) {
        $error=$e->getMessage();
        }
   if(!empty($error)) echo rex_view::error($error);
   }
public static function define_module($mypackage) {
   #   defining the module source and returning it as array:
   #      $mod['name']     the module's name
   #      $mod['input']    source of the module's input part
   #      $mod['output']   source of the module's output part
   #   $mypackage          package name
   #
   $name='Member Login Page ('.$mypackage.')';
   $in='<?php
$clang_id=rex_clang::getCurrentId();
$clang_code=rex_clang::get($clang_id)->getCode();
if($clang_code=="de")
  echo "<p>Anzeige einer Login-Seite zur Authentifizierung als Mitglieds-Benutzer</p>\n";
if($clang_code=="en")
  echo "<p>Displaying a login page for authentication as member user</p>\n";
?>';
   $out='<?php
access_control::login_page();
?>';
   return array('name'=>$name, 'input'=>str_replace('\\','\\\\',$in), 'output'=>str_replace('\\','\\\\',$out));
   }
public static function build_module($mypackage) {
   #   creating / updating a module in table rex_module
   #   $mypackage          package name
   #   functions used:
   #      self::define_module($mypackage)
   #
   $table='rex_module';
   #
   # --- module source: name input, output
   $modul=self::define_module($mypackage);
   $name  =$modul['name'];
   $input =$modul['input'];
   $output=$modul['output'];
   #
   # --- module exists already?
   $sql=rex_sql::factory();
   $query='SELECT * FROM '.$table.' WHERE name LIKE \'%'.$mypackage.'%\'';
   $mod=$sql->getArray($query);
   if(!empty($mod)):
     #     existing:         update (name unchanged)
     $id=$mod[0]['id'];
     counter_sql_action($sql,'UPDATE '.$table.' SET  input=\''.$input.'\'  WHERE id='.$id);
     counter_sql_action($sql,'UPDATE '.$table.' SET output=\''.$output.'\' WHERE id='.$id);
     else:
     #     not yet existing: insert
     counter_sql_action($sql,'INSERT INTO '.$table.' (name,input,output) '.
        'VALUES (\''.$name.'\',\''.$input.'\',\''.$output.'\')');
     endif;
   }
}
?>