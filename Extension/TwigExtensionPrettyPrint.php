<?php

namespace RedpillLinpro\NosqlBundle\Extension;
  
/*
 * Pretty printer for the NoSqlBundle arrays / objects.
 * 
 */

class TwigExtensionPrettyPrint extends \Twig_Extension
{
   
   public function getFilters()
   {
  
        return array(
            'prettyprint' => new \Twig_Filter_Function('\RedpillLinpro\NosqlBundle\Extension\twig_pretty_print_filter', 
                array('needs_environment' => true)),

        );
    }
  
    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'pretty_print';
    }

}  

function pretty($value) 
{
    echo "<table>\n";
    foreach($value as $key => $value) {
        
        echo "<tr>\n<th valign='top'>$key</th>\n<td>";
        if (is_array($value)) { 
            pretty($value); 
        } else {
            echo $value . "\n";
        }

        echo "</td>\n</tr>\n";

    }
    echo "</table>\n";
    
}

function twig_pretty_print_filter(\Twig_Environment $env, $value, $length = 80, $separator = "\n", $preserve = false)
{
        pretty($value);
return;
}

