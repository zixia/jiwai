<?php

/**
 * JiWai.de Console Class
 * AKA Inc.
 */
class JWConsole {
    static public function cmdline() {
        global $argv;
        $s = '';
        foreach ($argv as $a) {
            if ($s) $s.= ' "'.$a.'"';
            else $s = '"'.$a.'"';
        }
        return $s;
    }
    static private $CODES  = array (
        'color' => array(
                'black'  => 30,
                'red'    => 31,
                'green'  => 32,
                'brown'  => 33,
                'blue'   => 34,
                'purple' => 35,
                'cyan'   => 36,
                'grey'   => 37,
                'yellow' => 33
        ),
        'style' => array(
                'normal'     => 0,
                'bold'       => 1,
                'light'      => 1,
                'underscore' => 4,
                'underline'  => 4,
                'blink'      => 5,
                'inverse'    => 6,
                'hidden'     => 8,
                'concealed'  => 8
        ),
        'background' => array(
                'black'  => 40,
                'red'    => 41,
                'green'  => 42,
                'brown'  => 43,
                'yellow' => 43,
                'blue'   => 44,
                'purple' => 45,
                'cyan'   => 46,
                'grey'   => 47
        )
    );

    /**
     * Returns an ANSI-Controlcode
     *
     * Takes 1 to 3 Arguments: either 1 to 3 strings containing the name of the
     * FG Color, style and BG color, or one array with the indices color, style
     * or background.
     *
     * @access public
     * @param mixed $color Optional
     *   Either a string with the name of the foreground color, or
     *   an array with the indices 'color', 'style', 'background' and
     *   corresponding names as values.
     * @param string $style Optional name of the style
     * @param string $background Optional name of the background color
     * @return string
     */
    function color($color=null, $style = null, $background = null) // {{{
    {
        $colors = &self::$CODES;
        if (is_array($color)) {
            if (isset($color['style'])) $style = @$color['style'];
            if (isset($color['background'])) $background = @$color['background'];
            if (isset($color['color'])) $color = @$color['color']; else unset($color);
        }
        if (isset($color) && $color == 'reset') {
            return "\033[0m";
        }
        $code = array();
        if (isset($color)) {
            $code[] = $colors['color'][$color];
        }
        if (isset($style)) {
            $code[] = $colors['style'][$style];
        }
        if (isset($background)) {
            $code[] = $colors['background'][$background];
        }
        if (empty($code)) {
            $code[] = 0;
        }
        $code = implode(';', $code);
        return "\033[{$code}m";
    } // }}}

    /**
     * Returns a FG color controlcode
     *
     * @access public
     * @param string $name
     * @return string
     */
    function fgcolor($name)
    {
        $colors = &self::$CODES;
        return "\033[".$colors['color'][$name].'m';
    }

    /**
     * Returns a style controlcode
     *
     * @access public
     * @param string $name
     * @return string
     */
    function style($name)
    {
        $colors = &self::$CODES;
        return "\033[".$colors['style'][$name].'m';
    }

    /**
     * Returns a BG color controlcode
     *
     * @access public
     * @param string $name
     * @return string
     */
    function bgcolor($name)
    {
        $colors = &self::$CODES;
        return "\033[".$colors['background'][$name].'m';
    }

    /**
     * Converts colorcodes in the format %y (for yellow) into ansi-control
     * codes. The conversion table is: ('bold' meaning 'light' on some
     * terminals). It's almost the same conversion table irssi uses.
     * <pre>
     *                  text      text            background
     *      ------------------------------------------------
     *      %k %K %0    black     dark grey       black
     *      %r %R %1    red       bold red        red
     *      %g %G %2    green     bold green      green
     *      %y %Y %3    yellow    bold yellow     yellow
     *      %b %B %4    blue      bold blue       blue
     *      %m %M %5    magenta   bold magenta    magenta
     *      %p %P       magenta (think: purple)
     *      %c %C %6    cyan      bold cyan       cyan
     *      %w %W %7    white     bold white      white
     *
     *      %F     Blinking, Flashing
     *      %U     Underline
     *      %8     Reverse
     *      %_,%9  Bold
     *
     *      %n     Resets the color
     *      %%     A single %
     * </pre>
     * First param is the string to convert, second is an optional flag if
     * colors should be used. It defaults to true, if set to false, the
     * colorcodes will just be removed (And %% will be transformed into %)
     *
     * @access public
     * @param string $string
     * @param bool $colored
     * @return string
     */
    function convert($string, $colored=true)
    {
        static $conversions = array ( // static so the array doesn't get built
                                      // everytime
            // %y - yellow, and so on... {{{
            '%y' => array('color' => 'yellow'),
            '%g' => array('color' => 'green' ),
            '%b' => array('color' => 'blue'  ),
            '%r' => array('color' => 'red'   ),
            '%p' => array('color' => 'purple'),
            '%m' => array('color' => 'purple'),
            '%c' => array('color' => 'cyan'  ),
            '%w' => array('color' => 'grey'  ),
            '%k' => array('color' => 'black' ),
            '%n' => array('color' => 'reset' ),
            '%Y' => array('color' => 'yellow',  'style' => 'light'),
            '%G' => array('color' => 'green',   'style' => 'light'),
            '%B' => array('color' => 'blue',    'style' => 'light'),
            '%R' => array('color' => 'red',     'style' => 'light'),
            '%P' => array('color' => 'purple',  'style' => 'light'),
            '%M' => array('color' => 'purple',  'style' => 'light'),
            '%C' => array('color' => 'cyan',    'style' => 'light'),
            '%W' => array('color' => 'grey',    'style' => 'light'),
            '%K' => array('color' => 'black',   'style' => 'light'),
            '%N' => array('color' => 'reset',   'style' => 'light'),
            '%3' => array('background' => 'yellow'),
            '%2' => array('background' => 'green' ),
            '%4' => array('background' => 'blue'  ),
            '%1' => array('background' => 'red'   ),
            '%5' => array('background' => 'purple'),
            '%6' => array('background' => 'cyan'  ),
            '%7' => array('background' => 'grey'  ),
            '%0' => array('background' => 'black' ),
            // Don't use this, I can't stand flashing text
            '%F' => array('style' => 'blink'),
            '%U' => array('style' => 'underline'),
            '%8' => array('style' => 'inverse'),
            '%9' => array('style' => 'bold'),
            '%_' => array('style' => 'bold')
            // }}}
        );
        if ($colored) {
            $string = str_replace('%%', '% ', $string);
            foreach($conversions as $key => $value) {
                $string = str_replace($key, self::color($value),
                          $string);
            }
            $string = str_replace('% ', '%', $string);

        } else {
            $string = preg_replace('/%((%)|.)/', '$2', $string);
        }
        return $string;
    }

    /**
     * Escapes % so they don't get interpreted as color codes
     *
     * @access public
     * @param string string
     * @return string
     */
    function escape($string) {
        return str_replace('%', '%%', $string);
    }

    /**
     * Strips ANSI color codes from a string
     *
     * @acess public
     * @param string string
     * @return string
     */
    function strip($string) {
        return preg_replace('/\033\[[\d;]+m/','',$string);
    }



        /**
        * Pauses execution until enter is pressed
        */
        public static function pause()
        {
            
            fgets(STDIN, 8192);
        }
        
        
        /**
        * Asks a boolean style yes/no question. Valid input is:
        *
        *  o Yes: 1/y/yes/true
        *  o No:  0/n/no/false
        *
        * @param string $question The string to print. Should be a yes/no
        *                         question the user can answer. The following
        *                         will be appended to the question:
        *                         "[Yes]/No"
        * @param bool   $default  The default answer if only enter is pressed.
        */
        public static function yesno($question, $default = null)
        {
            if (!is_null($default)) {
                $defaultStr = $default ? '[Yes]/No' : 'Yes/[No]';
            } else {
                $defaultStr = 'Yes/No';
            }
            
            $fp = STDIN;
            
            while (true) {
                echo $question, " ", $defaultStr, ": ";
                $response = trim(fgets($fp, 8192));
                
                if (!is_null($default) AND $response == '') {
                    return $default;
                }
    
                switch (strtolower($response)) {
                    case 'y':
                    case '1':
                    case 'yes':
                    case 'true':
                        return true;
                    
                    case 'n':
                    case '0':
                    case 'no':
                    case 'false':
                        return false;
                    
                    default:
                        continue;
                }
            }
        }
    

        /**
        * Clears the screen. Specific to Linux (and possibly bash too)
        */
        public static function clear()
        {
            echo chr(033), "c";
        }
        
        
        /**
        * Returns a line of input from the screen with the corresponding
        * LF character appended (if appropriate).
        *
        * @param int $buffer Line buffer. Defaults to 8192
        */
        public static function getline($buffer = 8192)
        {
            return fgets(STDIN, $buffer);
        }


        /**
        * Shows a console menu
        *
        * @param array $items The menu items. Should be a two dimensional array. 2nd
        *                     dimensional should be associative containing the following
        *                     keys:
        *                      o identifier - The key/combo the user should enter to activate
        *                                     this menu. Usually a single character or number.
        *                                     This is lower cased when used for comparison, so
        *                                     mixing upper/lower case identifiers will not work.
        *                      o text       - The description associated with this menu item.
        *                      o callback   - Optional. If specified this callback is called
        *                                     using call_user_func(). If not specified then the
        *                                     identifier is returned instead, (after the callback
        *                                     has run the identifier is returned also). The callback
        *                                     is given one argument, which is the identifier of the
        *                                     menu item.
        * @param bool  $clear Whether to clear the screen before printing the menu.
        *                     Defaults to false.
        */
        public static function menu($items, $clear = false)
        {
            // Find the longest identifier
            $max_length = 0;
            foreach ($items as $k => $v) {
                $identifiers[strtolower($v['identifier'])] = $k;
                $max_length  = max(strlen($v['identifier']), $max_length);
            }
            
            while (true) {
                if ($clear) {
                    self::clear();
                }
                
                echo "Please select one of the following options:\n\n";
                
                // Print the menu
                foreach ($items as $k => $v) {
                    echo str_pad($v['identifier'], $max_length, ' ', STR_PAD_LEFT), ") ", $v['text'], "\n";
                }
                
                echo "\nSelect: ";
                $input = trim(strtolower(self::GetLine()));

                // Invalid menu item chosen
                if (!isset($identifiers[$input])) {
                    echo "Invalid menu item chosen...\n";
                    sleep(1);
                    continue;
                
                // Valid menu item chosen
                } else {
                    $item = $items[$identifiers[$input]];
                    if (!empty($item['callback']) AND is_callable($item['callback'])) {
                        call_user_func($item['callback'], $input);
                    }
                    
                    return $input;
                }
            }
        }
}
?>
