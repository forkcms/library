<?php

/**
 * Spoon Library
 *
 * This source file is part of the Spoon Library. More information,
 * documentation and tutorials can be found @ http://www.spoon-library.com
 *
 * @package        spoon
 * @subpackage    exception
 *
 *
 * @author        Davy Hellemans <davy@spoon-library.com>
 * @since        0.1.1
 */

/**
 * This is the default spoon exception, that extends the default php exception
 *
 * @package        spoon
 * @subpackage    exception
 *
 *
 * @author        Davy Hellemans <davy@spoon-library.com>
 * @author        Dieter Vanden Eynde <dieter@dieterve.be>
 * @since        0.1.1
 */
class SpoonException extends Exception
{
    /**
     * Exception name
     *
     * @var    string
     */
    protected $name;
    /**
     * String to obfuscate
     *
     * @var    array
     */
    protected $obfuscate = [];

    /**
     * Class constructor.
     *
     * @param    string $message The message that should be used.
     * @param    int[optional] $code            A numeric code for the exceptions.
     * @param    mixed[optional] $obfuscate    The string(s) that will be obfuscated.
     */
    public function __construct($message, $code = 0, $obfuscate = null)
    {
        // parent constructor
        parent::__construct((string) $message, (int) $code);

        // set name
        $this->name = static::class;

        // obfuscating?
        if ($obfuscate !== null) {
            $this->obfuscate = (array) $obfuscate;
        }
    }

    /**
     * Retrieve the name of this exception.
     *
     * @return    string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Return an array of elements that need to be obfuscated.
     *
     * @return    array
     */
    public function getObfuscate()
    {
        return $this->obfuscate;
    }
}

// Redefine the exception handler if we are not running in the command line.
if (!Spoon::inCli()) {
    set_exception_handler(exceptionHandler(...));
}

/**
 * Prints out the thrown exception in a more readable manner for a person using
 * a web browser.
 *
 * @param    SpoonException $exception
 */
function exceptionHandler($exception)
{
    // fetch trace stack
    $trace = $exception->getTrace();

    // specific name
    $name = (method_exists($exception, 'getName')) ? $exception->getName() : $exception::class;

    // spoon type exception
    if (method_exists($exception, 'getName') && strtolower(substr($exception->getName(), 0, 5)) == 'spoon' && $exception->getCode() != 0) {
        $documentation = '&raquo; <a href="http://www.spoon-library.com/exceptions/detail/' . $exception->getCode() . '">view documentation</a>';
    }

    // request uri?
    if (!isset($_SERVER['HTTP_HOST'])) {
        $_SERVER['HTTP_HOST'] = '';
    }
    if (!isset($_SERVER['REQUEST_URI'])) {
        $_SERVER['REQUEST_URI'] = '';
    }
    if (!isset($_SERVER['REQUEST_METHOD'])) {
        $_SERVER['REQUEST_METHOD'] = '';
    }

    // user agent
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '<i>(Unknown)</i>';

    // custom callback?
    if (Spoon::getExceptionCallback() != '') {
        // function
        if (!strpos(Spoon::getExceptionCallback(), '::')) {
            // function actually has been defined
            if (function_exists(Spoon::getExceptionCallback())) {
                call_user_func_array(Spoon::getExceptionCallback(), [$exception, getOutput($exception)]);
            } // something went wrong
            else {
                exit('The exception callback function (' . Spoon::getExceptionCallback() . ') could not be found.');
            }
        } else {
            // method
            $method = explode('::', Spoon::getExceptionCallback());

            // 2 parameters and exists
            if (count($method) == 2 && is_callable([$method[0], $method[1]])) {
                call_user_func_array([$method[0], $method[1]], [$exception, getOutput($exception)]);
            } // something went wrong
            else {
                exit('The exception callback function (' . Spoon::getExceptionCallback() . ') cound not be found.');
            }
        }
    } else {
        // on CLI we have no use for 2000 lines long report, show short info
        if (Spoon::inCli()) {
            echo $name . "\n";
            echo 'Message: ' . $exception->getMessage() . "\n";
            echo 'File: ' . $exception->getFile() . "\n";
            echo 'Line: ' . $exception->getLine() . "\n";
        } // debugging enabled (show output)
        elseif (Spoon::getDebug()) {
            echo getOutput($exception);
        } // debugging disabled
        else {
            echo Spoon::getDebugMessage();
        }
    }

    // mail it?
    if (Spoon::getDebugEmail() != '') {
        // e-mail headers
        $headers = "MIME-Version: 1.0\n";
        $headers .= "Content-type: text/html; charset=iso-8859-15\n";
        $headers .= "X-Priority: 3\n";
        $headers .= "X-MSMail-Priority: Normal\n";
        $headers .= "X-Mailer: SpoonLibrary Webmail\n";
        $headers .= "From: Spoon Library <no-reply@spoon-library.com>\n";

        // send email
        @mail(Spoon::getDebugEmail(), 'Exception Occured', (string) getOutput($exception), $headers);
    }

    // stop script execution
    exit;
}

function getOutput($exception)
{
    // specific name
    $name = (method_exists($exception, 'getName')) ? $exception->getName() : $exception::class;
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '<i>(Unknown)</i>';

    // generate output
    $output = '
	<html>
		<head>
			<title>' . $name . '</title>
		</head>
		<body style="background-color: #F2F2F2; color: #0000000, font-family: Verdana, Tahoma, Arial; font-size 10px; margin: 0; padding: 0;">
			<table width="100%">
				<tr>
					<td style="vertical-align: top; font-family: Verdana, Tahoma, Arial; font-size: 10px; color: #000000;">&nbsp;</td>
					<td style="width: 550px">
						<table width="550px;">
							<tr>
								<td style="background-color: #EEEEEE; border: 1px solid #B2B2B2;">
									<h1 style="font-size: 12px; margin: 5px 5px 12px 5px; padding: 0 0 5px 0; color: #000000; font-family: Verdana, Tahoma, Arial; border-bottom: 1px solid #999999;">' . $name . ' &raquo; Main</h1>
									<table width="550px">
										<tr>
											<th width="110px" style="vertical-align: top; text-align: left; font-weight: 700; padding: 0 0 0 10px; font-family: Verdana, Tahoma, Arial; font-size: 10px; color: #000000;">Message</th>
											<td style="vertical-align: top; font-family: Verdana, Tahoma, Arial; font-size: 10px; color: #000000;">' . $exception->getMessage() . '</td>
										</tr>
										<tr>
											<th width="110px" style="vertical-align: top; text-align: left; font-weight: 700; padding: 0 0 0 10px; font-family: Verdana, Tahoma, Arial; font-size: 10px; color: #000000;">File</th>
											<td style="vertical-align: top; font-family: Verdana, Tahoma, Arial; font-size: 10px; color: #000000;">' . dimCommonPathPrefix($exception->getFile()) . '</td>
										</tr>
										<tr>
											<th width="110px" style="vertical-align: top; text-align: left; font-weight: 700; padding: 0 0 0 10px; font-family: Verdana, Tahoma, Arial; font-size: 10px; color: #000000;">Line</th>
											<td style="vertical-align: top; font-family: Verdana, Tahoma, Arial; font-size: 10px; color: #000000;">' . $exception->getLine() . '</td>
										</tr>
										<tr>
											<th width="110px" style="vertical-align: top; text-align: left; font-weight: 700; padding: 0 0 0 10px; font-family: Verdana, Tahoma, Arial; font-size: 10px; color: #000000;">Date</th>
											<td style="vertical-align: top; font-family: Verdana, Tahoma, Arial; font-size: 10px; color: #000000;">' . date('r') . '</td>
										</tr>
										<tr>
											<th width="110px" style="vertical-align: top; text-align: left; font-weight: 700; padding: 0 0 0 10px; font-family: Verdana, Tahoma, Arial; font-size: 10px; color: #000000;">URL</th>
											<td style="vertical-align: top; font-family: Verdana, Tahoma, Arial; font-size: 10px; color: #000000;">' . "\n";
    // request URL
    $output .= '							<a href="http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . '">http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . '</a>' . "\n";
    $output .= '						</td>
										</tr>
										<tr>
											<th width="110px" style="vertical-align: top; text-align: left; font-weight: 700; padding: 0 0 0 10px; font-family: Verdana, Tahoma, Arial; font-size: 10px; color: #000000;">Referring URL</th>
											<td style="vertical-align: top; font-family: Verdana, Tahoma, Arial; font-size: 10px; color: #000000;">' . "\n";
    // referring URL
    if (isset($_SERVER['HTTP_REFERER']) && $_SERVER['HTTP_REFERER'] != '') {
        $output .= '						<a href="' . $_SERVER['HTTP_REFERER'] . '">' . $_SERVER['HTTP_REFERER'] . '</a>' . "\n";
    } else {
        $output .= '						<i>(Unknown)</i>' . "\n";
    }

    $output .= '						</td>
										</tr>
										<tr>
											<th width="110px" style="vertical-align: top; text-align: left; font-weight: 700; padding: 0 0 0 10px; font-family: Verdana, Tahoma, Arial; font-size: 10px; color: #000000;">Request Method</th>
											<td style="vertical-align: top; font-family: Verdana, Tahoma, Arial; font-size: 10px; color: #000000;">' . $_SERVER['REQUEST_METHOD'] . '</td>
										</tr>
										<tr>
											<th width="110px" style="vertical-align: top; text-align: left; font-weight: 700; padding: 0 0 0 10px; font-family: Verdana, Tahoma, Arial; font-size: 10px; color: #000000;">User-agent</th>
											<td style="vertical-align: top; font-family: Verdana, Tahoma, Arial; font-size: 10px; color: #000000;">' . $userAgent . '</td>
										</tr>';
    // no documentation ?
    if (isset($documentation)) {
        $output .= '				<tr>
											<th width="110px" style="vertical-align: top; text-align: left; font-weight: 700; padding: 0 0 0 10px; font-family: Verdana, Tahoma, Arial; font-size: 10px; color: #000000;">Documentation</th>
											<td style="vertical-align: top; font-family: Verdana, Tahoma, Arial; font-size: 10px; color: #000000;">' . $documentation . '</td>
										</tr>';
    }

    // we know about the last error
    if (error_get_last() !== null) {
        // define message
        $error = error_get_last();

        // show output
        $output .= '				<tr>
											<th width="110px" style="vertical-align: top; text-align: left; font-weight: 700; padding: 0 0 0 10px; font-family: Verdana, Tahoma, Arial; font-size: 10px; color: #000000;">PHP error</th>
											<td style="vertical-align: top; font-family: Verdana, Tahoma, Arial; font-size: 10px; color: #000000;">' . $error['message'] . '</td>
										</tr>';
    }

    $output .= '				</table>
								</td>
							</tr>
							<tr>
								<td style="vertical-align: top; font-family: Verdana, Tahoma, Arial; font-size: 10px; color: #000000;">&nbsp;</td>
							</tr>
							<!-- stack -->
							<tr>
								<td style="background-color: #EEEEEE; border: 1px solid #B2B2B2;">
									<h1 style="font-size: 12px; margin: 5px 5px 12px 5px; padding: 0 0 5px 0; color: #000000; font-family: Verdana, Tahoma, Arial; border-bottom: 1px solid #999999;">' . $name . ' &raquo; Trace</h1>
									<table width="550px;">' . "\n";

    // trace has items
    if (count($exception->getTrace()) != 0) {
        // fetch entire stack
        $entireTraceStack = $exception->getTrace();

        // loop elements
        foreach ($entireTraceStack as $traceStack) {
            // open defintion list
            $output .= '			<tr>
											<td style="vertical-align: top; font-family: Verdana, Tahoma, Arial; font-size: 10px; color: #000000;">
												<table width="550px;" style="border-top: 1px dotted; padding-top: 1ex;">
													<tr>
														<th width="110px" style="vertical-align: top; text-align: left; font-weight: 700; padding: 0 0 0 10px; font-family: Verdana, Tahoma, Arial; font-size: 10px; color: #000000;">File</th>
														<td style="vertical-align: top; font-family: Verdana, Tahoma, Arial; font-size: 10px; color: #000000;">' . ((isset($traceStack['file'])) ? dimCommonPathPrefix($traceStack['file']) : '<i>(Unknown)</i>') . '
														</td>
													</tr>
													<tr>
														<th width="110px" style="vertical-align: top; text-align: left; font-weight: 700; padding: 0 0 0 10px; font-family: Verdana, Tahoma, Arial; font-size: 10px; color: #000000;">Line</th>
														<td style="vertical-align: top; font-family: Verdana, Tahoma, Arial; font-size: 10px; color: #000000;">' . ($traceStack['line'] ?? '<i>(Unknown)</i>') . '
														</td>
													</tr>';

            // class & function
            if (isset($traceStack['class'])) {
                $docUrl = 'http://www.google.com/search?btnI=&ie=utf-8&sourceid=navclient&q=' . urlencode(sprintf('site:php.net/manual OR site:www.spoon-library.com/docs class "%s"', $traceStack['class']));
                $link = sprintf('<a href="%s">%s</a>', htmlspecialchars($docUrl), htmlspecialchars($traceStack['class']));
                $output .= '					<tr>
														<th width="110px" style="vertical-align: top; text-align: left; font-weight: 700; padding: 0 0 0 10px; font-family: Verdana, Tahoma, Arial; font-size: 10px; color: #000000;">Class</th>
														<td style="vertical-align: top; font-family: Verdana, Tahoma, Arial; font-size: 10px; color: #000000;">' . $link . '</td>
													</tr>';
            }
            if (isset($traceStack['function'])) {
                $docUrl = isset($traceStack['class'])
                    ? 'http://www.google.com/search?btnI=&ie=utf-8&sourceid=navclient&q=' . urlencode(sprintf('site:php.net/manual OR site:www.spoon-library.com/docs "%s::%s"', $traceStack['class'], $traceStack['function']))
                    : 'http://www.google.com/search?btnI=&ie=utf-8&sourceid=navclient&q=' . urlencode(sprintf('site:php.net/manual OR site:www.spoon-library.com/docs "%s"', $traceStack['function']));
                $link = sprintf('<a href="%s">%s</a>', htmlspecialchars($docUrl), htmlspecialchars($traceStack['function']));
                $output .= '					<tr>
														<th width="110px" style="vertical-align: top; text-align: left; font-weight: 700; padding: 0 0 0 10px; font-family: Verdana, Tahoma, Arial; font-size: 10px; color: #000000;">Function</th>
														<td style="vertical-align: top; font-family: Verdana, Tahoma, Arial; font-size: 10px; color: #000000;">' . $link . '</td>
													</tr>';
            }

            // close defintion list
            $output .= '					</table>
											</td>
										</tr>';
        }
    } // no trace
    else {
        $output .= '				<tr>
											<td style="vertical-align: top; font-family: Verdana, Tahoma, Arial; font-size: 10px; color: #000000;">No trace available.</td>
										</tr>';
    }

    // output the superglobal variables, if any
    $hasVars = false;
    foreach (['GET', 'POST', 'COOKIE', 'FILES'] as $superGlobal) {
        $hasVars |= count($GLOBALS['_'. $superGlobal ]);
    }

    if ($hasVars) {
        $output .= '				</table>
									</td>
								<tr>
								<tr>
									<td style="vertical-align: top; font-family: Verdana, Tahoma, Arial; font-size: 10px; color: #000000;">&nbsp;</td>
								</tr>
								<!-- variables -->
								<tr>
									<td style="background-color: #EEEEEE; border: 1px solid #B2B2B2;">
										<h1 style="font-size: 12px; margin: 5px 5px 12px 5px; padding: 0 0 5px 0; color: #000000; font-family: Verdana, Tahoma, Arial; border-bottom: 1px solid #999999;">' . $name . ' &raquo; Variables</h1>
										<table width="550px;">' . "\n";
    }

    // continue output generation
    $output .= '				</table>
								</td>
							</tr>
						</table>
					</td>
					<td style="vertical-align: top; font-family: Verdana, Tahoma, Arial; font-size: 10px; color: #000000;">&nbsp;</td>
				</tr>
			</table>
		</body>
	</html>
	';

    // obfuscate
    if (method_exists($exception, 'getObfuscate') && count($exception->getObfuscate()) != 0) {
        $output = str_replace($exception->getObfuscate(), '***', $output);
    }

    return $output;
}

/**
 * Helper function for the exception handler to pretty-print variables.
 *
 * @return    string
 *
 * @param    mixed $var A variable to dump
 * @param    mixed[optional] $varN    Zero or more variables to dump.
 */
function exceptionHandlerDumper($var)
{
    ob_start();
    foreach (func_get_args() as $arg) {
        if (is_string($arg)) {
            echo $arg;
        } else {
            ob_start();
            var_dump($arg);
            $output = ob_get_clean();
            echo substr($output, 0, -1);
        }
    }
    $dump = ob_get_clean();

    return preg_replace(
        ['@{\n}@', '@#[0-9]+@', '@\]=>\n\s*@'],
        ['{ }', '', '] => '],
        $dump
    );
}

/**
 * Get the HTML with the longest common path for this file and the given
 * path dimmed.
 *
 * @param    string $path The path to compare against this file's path.
 *
 * @return    string
 */
function dimCommonPathPrefix($path)
{
    // determine the longest shared prefix
    $prefix = __DIR__;
    while (!empty($prefix) && $prefix !== '/' && $prefix !== '.') {
        if (str_starts_with($path, $prefix . DIRECTORY_SEPARATOR)) {
            break;
        }
        $prefix = dirname($prefix);
    }

    // generate the HTML to dim the prefix, if any
    $html = empty($prefix) || $prefix === '/' || $prefix === '.'
        ? htmlspecialchars($path)
        : sprintf(
            '<span style="opacity: 0.33">%s/</span>%s',
            htmlspecialchars($prefix),
            htmlspecialchars(substr($path, strlen($prefix) + 1))
        );

    // insert word break hints to avoid overly long lines
    $html = preg_replace('@[^<]/@', '$0<wbr/>', $html);

    return $html;
}
