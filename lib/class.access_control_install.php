<?php
/**
 * Access Control AddOn
 * @author wolfgang[at]busch-dettum[dot]de Wolfgang Busch
 * @package redaxo5
 * @version Juli 2018
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
        $error="";
         } catch(rex_sql_exception $e) {
        $error=$e->getMessage();
        }
   if(!empty($error)) echo rex_view::error($error);
   }
public static function define_modul_out() {
   #   returning the module source code (output part)
   #
   return
'<?php
access_control::login_page();
?>';
   }
public static function insert_module($mypackage) {
   #   creating the module (output part)
   #   functions used:
   #      self::define_modul_out()
   #      self::sql_action($sql,$query)
   #
   # --- module content (output)
   $out=self::define_modul_out();
   $sql=rex_sql::factory();
   $table="rex_module";
   $modname="Member Login Page (".$mypackage.")";
   #
   # --- module exists already?
   $query="SELECT * FROM ".$table." WHERE name LIKE '%".$mypackage."%'";
   $mod=$sql->getArray($query);
   if(count($mod[0])>0):
     #     existing:         update
     self::sql_action($sql,"UPDATE ".$table." SET output='".$out."' ".
        "WHERE id=".$mod[0][id]);
     else:
     #     not yet existing: insert
     self::sql_action($sql,"INSERT INTO ".$table." (name,input,output) ".
        "VALUES ('".$modname."','','".$out."')");
     endif;
   }
}
?>
