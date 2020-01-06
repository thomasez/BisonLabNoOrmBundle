<?php

namespace BisonLab\NoOrmBundle\Extension;
use Twig\Extension\AbstractExtension;
use Twig\Environment as TwigEnvironment;
  
/*
 * Pretty printer for the NoSqlBundle arrays / objects.
 * 
 */

class TwigExtensionPrettyPrint extends AbstractExtension
{
   
   public function getFilters()
   {
  
        return array(
            'prettyprint' => new \Twig_Filter_Function('\BisonLab\NoOrmBundle\Extension\twig_pretty_print_filter', 
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
    if (empty($value)) { return ""; }

    echo "<table>\n";
    foreach($value as $key => $value) {
        
        echo "<tr>\n<th valign='top'>$key</th>\n<td>";
        if (is_array($value)) { 
            pretty($value); 
        } else {
            // I want to change \n to <br />. Not perfect but I need it.
            $value = preg_replace("/\n/", "<br />", $value);
            echo $value . "\n";
        }

        echo "</td>\n</tr>\n";

    }
    echo "</table>\n";
    
}

function twig_pretty_print_filter(TwigEnvironment $env, $value, $length = 80, $separator = "\n", $preserve = false)
{
        pretty($value);
return;
}

