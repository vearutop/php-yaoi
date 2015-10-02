<?php

namespace YaoiTests\PHPUnit\Debug;

use Yaoi\Debug\CollapsiblePrintR;
use Yaoi\Test\PHPUnit\TestCase;

class CollapsiblePrintRTest extends TestCase
{
    public function testArray()
    {
        $result = CollapsiblePrintR::create(array('a' => array('b' => 'c')));
        $result = trim(str_replace("\r", '', $result));

        $expected = <<<'EOD'
<pre>Array
(
    [a] => Array
        (
            [b] => c
        )

)
</pre><script type="text/javascript">(function(e){function n(e){return e.replace(/(Array|Object)\n(\s*)\(/g,'<span class="debug-controls"><a class="toggle-display" href="#">$1</a> '+'<a class="toggle-children" href="#" title="toggle children">\\</a> '+'<a class="toggle-recursive" href="#" title="toggle recursive">*</a> '+'</span><span class="debug-data" style="display:none"> '+"\n$2(").replace(/\n(\s*?)\)\n/g,"\n$1)\n</span>")}function r(e,t){if("undefined"==typeof t){t=i(e,0)}e.style.display=t?"":"none";return t}function i(e,t){var n;if(!t){return e.style.display=="none"}else{for(var r=0;r<e.childNodes.length;++r){if("debug-data"==e.childNodes[r].className){n=i(e.childNodes[r],t-1);if(-1!=n){return n}}}}return-1}function s(e,t,n){if("undefined"==typeof n){n=i(e,t?2:1);if(-1==n){if(t){s(e,false);return}else{r(e);return}}}for(var o=0;o<e.childNodes.length;++o){if("debug-data"==e.childNodes[o].className){r(e.childNodes[o],n);if(t){s(e.childNodes[o],true,n)}}}if(n){r(e,n)}}function o(e){e.innerHTML=n(e.innerHTML);var t=e.getElementsByTagName("a");for(var i=0;i<t.length;++i){t[i].onclick=function(){var e=this.className,t=this.parentNode.nextSibling;if(e=="toggle-display"){r(t)}else if(e=="toggle-children"){s(t,false)}else if(e=="toggle-recursive"){s(t,true)}return false}}if("none"==e.style.display){e.style.display=""}}if("undefined"==typeof e){var t=document.getElementsByTagName("script");e=t[t.length-1].previousSibling}o(e)})()</script>
EOD;
        $expected = trim(str_replace("\r", '', $expected));

        $this->assertSame($expected, $result);
    }
}