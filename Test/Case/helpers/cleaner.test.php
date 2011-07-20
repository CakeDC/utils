<?php
App::import('Core', 'Helper', false);
App::import('Helper', 'AppHelper', false);
App::import('Core', 'View', false);
App::import('Helper', 'Utils.Cleaner');

/**
 * Short description for class.
 *
 * @package		cake.tests
 * @subpackage	cake.tests.cases.libs
 */
class CleanerHelperTest extends CakeTestCase {

	function setUp() {
		ClassRegistry::flush();
		Router::reload();
		$null = null;
		$this->View = new View($null);
		$this->Helper = new Helper($this->View);
		$this->Cleaner = new CleanerHelper($this->View);
	}

	function testClean() {
		$tagsArray = array('br', 'p', 'strong', 'em', 'ul', 'ol', 'li', 'dl'	, 'dd', 'dt', 'a', 'img', 'i', 'u', 'b');
		$attributesArray = array('src', 'href', 'title');
		$replaceImgThumb = false;
		$this->Cleaner->configure(compact('tagsArray', 'attributesArray', 'replaceImgThumb'));

		$result = $this->Cleaner->clean('<p><img src="/bla/bla"></p> <iframe>data</iframe> <i>data</i>');
		$this->assertEqual($result, '<p><img src="/bla/bla" /></p> data <i>data</i>');

		$result = $this->Cleaner->clean('<body>data</body></div></div></div>');
		$this->assertEqual($result, 'data');

		$result = $this->Cleaner->clean('<i>data</i><div class="test"></div>');
		$this->assertEqual($result, '<i>data</i>');

		$result = $this->Cleaner->clean('<b><i>data</i><div class="test"></b>');
		$this->assertEqual($result, '<b><i>data</i></b>');

		$result = $this->Cleaner->clean('<p style="blink">data</p>');
		$this->assertEqual($result, '<p>data</p>');

		$result = $this->Cleaner->clean('<a href="#" onclick="javascr">data</a>');
		$this->assertEqual($result, '<a href="#">data</a>');

		$result = $this->Cleaner->clean('<img src="www.example.com/img.jpg" />data');
		$this->assertEqual($result, '<img src="www.example.com/img.jpg" />data');

		$result = $this->Cleaner->clean('<img src="/media/display/47ce0324-2238-40ec-b68a-a7994a35e6b2" />data');
		$this->assertEqual($result, '<img src="/media/display/47ce0324-2238-40ec-b68a-a7994a35e6b2" />data');

		$replaceImgThumb = true;
		$settings = compact('tagsArray', 'attributesArray', 'replaceImgThumb');
		$result = $this->Cleaner->clean('<img src="/media/display/47ce0324-2238-40ec-b68a-a7994a35e6b2" />data', $settings);
		$this->assertEqual($result, '<img src="/media/display/thumb/47ce0324-2238-40ec-b68a-a7994a35e6b2" />data');

	}
	function _testImgThumb() {
		$tagsArray = array('br', 'p', 'strong', 'em', 'ul', 'ol', 'li', 'dl', 'dd', 'dt', 'a', 'img', 'i', 'u', 'b');
		$attributesArray = array('src', 'href', 'title');
		$replaceImgThumb = false;
		$settings = compact('tagsArray', 'attributesArray', 'replaceImgThumb');
		$result = $this->Cleaner->replaceAllImageTags('<img src="/media/display/47ce0324-2238-40ec-b68a-a7994a35e6b2" />data');
		$this->assertEqual($result, '<img src="/media/display/thumb/47ce0324-2238-40ec-b68a-a7994a35e6b2" />data');
		$result = $this->Cleaner->replaceAllImageTags('<img src="/media/display/47ce0324-2238-40ec-b68a-a7994a35e6b2" /><img src="/media/display/47ce0324-2238-40ec-b68a-a7994a35e6b2" /><img src="/media/display/47ce0324-2238-40ec-b68a-a7994a35e6b2" />data');
		$this->assertEqual($result, '<img src="/media/display/thumb/47ce0324-2238-40ec-b68a-a7994a35e6b2" /><img src="/media/display/thumb/47ce0324-2238-40ec-b68a-a7994a35e6b2" /><img src="/media/display/thumb/47ce0324-2238-40ec-b68a-a7994a35e6b2" />data');
		$result = $this->Cleaner->replaceAllImageTags('<img src="/media/display/47ce0324-2237-40ec-b68a-a7994a35e6b2" /><img src="/media/display/47ce0324-2238-40ec-b68a-a7994a35e6b2" /><img src="/media/display/47ce0324-2239-40ec-b68a-a7994a35e6b2" />data');
		$this->assertEqual($result, '<img src="/media/display/thumb/47ce0324-2237-40ec-b68a-a7994a35e6b2" /><img src="/media/display/thumb/47ce0324-2238-40ec-b68a-a7994a35e6b2" /><img src="/media/display/thumb/47ce0324-2239-40ec-b68a-a7994a35e6b2" />data');

	}

	function testBbcode2js() {
		$result = $this->Cleaner->bbcode2js('[googlevideo]http://video.google.com/videoplay?docid=S1IjqhLFv5bl07AWays[/googlevideo]');
		$this->assertEqual($result, '<p id="vvq_S1IjqhLFv5bl07AWays"><a href="http://video.google.com/videoplay?docid=S1IjqhLFv5bl07AWays">http://video.google.com/videoplay?docid=S1IjqhLFv5bl07AWays</a></p><br />');

		$result = $this->Cleaner->bbcode2js('[googlevideo]http://video.google.com/videoplay?docid=e2HwisfuVokRIKTXCa7[/googlevideo]');
		$this->assertEqual($result, '<p id="vvq_e2HwisfuVokRIKTXCa7"><a href="http://video.google.com/videoplay?docid=e2HwisfuVokRIKTXCa7">http://video.google.com/videoplay?docid=e2HwisfuVokRIKTXCa7</a></p><br />');

		$result = $this->Cleaner->bbcode2js('[googlevideo]http://video.google.com/videoplay?docid=1Y1bxKUBOVAoCENNQTj[/googlevideo]');
		$this->assertEqual($result, '<p id="vvq_1Y1bxKUBOVAoCENNQTj"><a href="http://video.google.com/videoplay?docid=1Y1bxKUBOVAoCENNQTj">http://video.google.com/videoplay?docid=1Y1bxKUBOVAoCENNQTj</a></p><br />');

		$result = $this->Cleaner->bbcode2js('[googlevideo]http://video.google.com/videoplay?docid=atVdRpDa1q7KzZpxtfA[/googlevideo]');
		$this->assertEqual($result, '<p id="vvq_atVdRpDa1q7KzZpxtfA"><a href="http://video.google.com/videoplay?docid=atVdRpDa1q7KzZpxtfA">http://video.google.com/videoplay?docid=atVdRpDa1q7KzZpxtfA</a></p><br />');

		$result = $this->Cleaner->bbcode2js('[googlevideo]http://video.google.com/videoplay?docid=-4131010848345121656[/googlevideo]');
		$this->assertEqual($result, '<p id="vvq_-4131010848345121656"><a href="http://video.google.com/videoplay?docid=-4131010848345121656">http://video.google.com/videoplay?docid=-4131010848345121656</a></p><br />');

		$result = $this->Cleaner->bbcode2js('[youtubevideo]http://www.youtube.com/watch?v=4O4x9J4PnSI[/youtubevideo]');
		$this->assertEqual($result, '<p id="vvq_4O4x9J4PnSI"><a href="http://www.youtube.com/watch?v=4O4x9J4PnSI">http://www.youtube.com/watch?v=4O4x9J4PnSI</a></p><br />');

		$result = $this->Cleaner->bbcode2js('[youtubevideo]http://www.youtube.com/watch?v=QTsXlTKaFq0[/youtubevideo]');
		$this->assertEqual($result, '<p id="vvq_QTsXlTKaFq0"><a href="http://www.youtube.com/watch?v=QTsXlTKaFq0">http://www.youtube.com/watch?v=QTsXlTKaFq0</a></p><br />');

		$result = $this->Cleaner->bbcode2js('[youtubevideo]http://www.youtube.com/watch?v=ebfxYAUBw-0[/youtubevideo]');
		$this->assertEqual($result, '<p id="vvq_ebfxYAUBw-0"><a href="http://www.youtube.com/watch?v=ebfxYAUBw-0">http://www.youtube.com/watch?v=ebfxYAUBw-0</a></p><br />');

		$result = $this->Cleaner->bbcode2js('[youtubevideo]http://www.youtube.com/watch?v=FSl742lZQe8[/youtubevideo]');
		$this->assertEqual($result, '<p id="vvq_FSl742lZQe8"><a href="http://www.youtube.com/watch?v=FSl742lZQe8">http://www.youtube.com/watch?v=FSl742lZQe8</a></p><br />');

		$result = $this->Cleaner->bbcode2js('[youtubevideo]http://www.youtube.com/watch?v=YPQ_N4imYVE[/youtubevideo]');
		$this->assertEqual($result, '<p id="vvq_YPQ_N4imYVE"><a href="http://www.youtube.com/watch?v=YPQ_N4imYVE">http://www.youtube.com/watch?v=YPQ_N4imYVE</a></p><br />');

		$result = $this->Cleaner->bbcode2js('[youtubevideo]http://www.youtube.com/watch?v=YPQ_N4imYVE[/youtubevideo]text[googlevideo]http://video.google.com/videoplay?docid=-4131010848345121656[/googlevideo]');
		$this->assertEqual($result, '<p id="vvq_YPQ_N4imYVE"><a href="http://www.youtube.com/watch?v=YPQ_N4imYVE">http://www.youtube.com/watch?v=YPQ_N4imYVE</a></p><br />text<p id="vvq_-4131010848345121656"><a href="http://video.google.com/videoplay?docid=-4131010848345121656">http://video.google.com/videoplay?docid=-4131010848345121656</a></p><br />');

		$result = $this->Cleaner->bbcode2js('[youtubevideo]http://www.youtube.com/watch?v=YPQ_N4imYVE[/youtubevideo]text[googlevideo]http://video.google.com/videoplay?docid=-4131010848345121656[/googlevideo]text[youtubevideo]http://www.youtube.com/watch?v=YPQ_N4imYVE[/youtubevideo]text[googlevideo]http://video.google.com/videoplay?docid=-4131010848345121656[/googlevideo]');
		$this->assertEqual($result, '<p id="vvq_YPQ_N4imYVE"><a href="http://www.youtube.com/watch?v=YPQ_N4imYVE">http://www.youtube.com/watch?v=YPQ_N4imYVE</a></p><br />text<p id="vvq_-4131010848345121656"><a href="http://video.google.com/videoplay?docid=-4131010848345121656">http://video.google.com/videoplay?docid=-4131010848345121656</a></p><br />text<p id="vvq_YPQ_N4imYVE"><a href="http://www.youtube.com/watch?v=YPQ_N4imYVE">http://www.youtube.com/watch?v=YPQ_N4imYVE</a></p><br />text<p id="vvq_-4131010848345121656"><a href="http://video.google.com/videoplay?docid=-4131010848345121656">http://video.google.com/videoplay?docid=-4131010848345121656</a></p><br />');

		$result = $this->Cleaner->bbcode2js('[youtubevideo]http://www.youtube.com/watch?v=YPQ_N4imYVE[/youtubevideo]text[googlevideo]http://video.google.com/videoplay?docid=-4131010848345121656[/googlevideo]text[youtubevideo]http://www.youtube.com/watch?v=YPQ_N4imYVE[/youtubevideo]text');
		$this->assertEqual($result, '<p id="vvq_YPQ_N4imYVE"><a href="http://www.youtube.com/watch?v=YPQ_N4imYVE">http://www.youtube.com/watch?v=YPQ_N4imYVE</a></p><br />text<p id="vvq_-4131010848345121656"><a href="http://video.google.com/videoplay?docid=-4131010848345121656">http://video.google.com/videoplay?docid=-4131010848345121656</a></p><br />text<p id="vvq_YPQ_N4imYVE"><a href="http://www.youtube.com/watch?v=YPQ_N4imYVE">http://www.youtube.com/watch?v=YPQ_N4imYVE</a></p><br />text');

		$result = $this->Cleaner->bbcode2js('[youtubevideo]http://www.youtube.com/watch?v=YPQ_N4imYVE[/youtubevideo][youtubevideo]http://www.youtube.com/watch?v=YPQ_N4imYVE[/youtubevideo]');
		$this->assertEqual($result, '<p id="vvq_YPQ_N4imYVE"><a href="http://www.youtube.com/watch?v=YPQ_N4imYVE">http://www.youtube.com/watch?v=YPQ_N4imYVE</a></p><br /><p id="vvq_YPQ_N4imYVE"><a href="http://www.youtube.com/watch?v=YPQ_N4imYVE">http://www.youtube.com/watch?v=YPQ_N4imYVE</a></p><br />');

		$result = $this->Cleaner->bbcode2js('[youtubevideo]http://www.youtube.com/watch?v=YPQ_N4imYVE[/youtubevideo]text[youtubevideo]http://www.youtube.com/watch?v=YPQ_N4imYVE[/youtubevideo]');
		$this->assertEqual($result, '<p id="vvq_YPQ_N4imYVE"><a href="http://www.youtube.com/watch?v=YPQ_N4imYVE">http://www.youtube.com/watch?v=YPQ_N4imYVE</a></p><br />text<p id="vvq_YPQ_N4imYVE"><a href="http://www.youtube.com/watch?v=YPQ_N4imYVE">http://www.youtube.com/watch?v=YPQ_N4imYVE</a></p><br />');
		
		$result = $this->Cleaner->bbcode2js('[googlevideo]http://video.google.com/videoplay?docid=atVdRpDa1q7KzZpxtfA[/googlevideo]', false);
		$this->assertEqual($result, '');
		
		$result = $this->Cleaner->bbcode2js('[youtubevideo]http://www.youtube.com/watch?v=YPQ_N4imYVE[/youtubevideo]text[youtubevideo]http://www.youtube.com/watch?v=YPQ_N4imYVE[/youtubevideo]', false);
		$this->assertEqual($result, 'text');

		$result = $this->Cleaner->bbcode2js('[breakvideo]http://embed.break.com/NTc3MjQ5[/breakvideo]');
		$this->assertEqual($result, '<object width="464" height="392"><param name="movie" value="http://embed.break.com/NTc3MjQ5"></param><param name="allowScriptAccess" value="always"></param><embed src="http://embed.break.com/NTc3MjQ5" type="application/x-shockwave-flash" allowScriptAccess=always width="464" height="392"></embed></object>');

		$result = $this->Cleaner->bbcode2js('[breakvideo]http://embed.break.com/NTc3MjQ5[/breakvideo]', false);
		$this->assertEqual($result, '');

		$result = $this->Cleaner->bbcode2js('[breakvideo]http://embed.break.com/NTc3MjQ5[/breakvideo]text', false);
		$this->assertEqual($result, 'text');

		$result = $this->Cleaner->bbcode2js('[breakvideo]http://embed.break.com/NTc3MjQ5[/breakvideo]text[breakvideo]http://embed.break.com/NTc3MjQ5[/breakvideo]', false);
		$this->assertEqual($result, 'text');

		$result = $this->Cleaner->bbcode2js('[breakvideo]http://embed.break.com/NTc3MjQ5[/breakvideo]text');
		$this->assertEqual($result, '<object width="464" height="392"><param name="movie" value="http://embed.break.com/NTc3MjQ5"></param><param name="allowScriptAccess" value="always"></param><embed src="http://embed.break.com/NTc3MjQ5" type="application/x-shockwave-flash" allowScriptAccess=always width="464" height="392"></embed></object>text');
	}

	function testOverCleaning() {
		$tagsArray = array('br', 'p', 'strong', 'em', 'ul', 'ol', 'li', 'dl'	, 'dd', 'dt', 'a', 'img', 'i', 'u', 'b');
		$attributesArray = array('src', 'href', 'title');
		$replaceImgThumb = false;
		$this->Cleaner->configure(compact('tagsArray', 'attributesArray', 'replaceImgThumb'));

		//$result = $this->Cleaner->clean('<p>4pm � 8pm Mountain of One</p>');
		//$this->assertEqual($result, '<p>4pm � 8pm Mountain of One</p>');
		
		$text = 'Single line spacing:
 
Noon-3pm Trojan Soundsystem  (LIVE)
3pm � 4pm Har Mar Superstar�s Desert Island Disco (pre rec)
4pm � 8pm Mountain of One
8pm � 11pm Shortwave Set (pre rec)
11pm � Midnight Inflagranti (pre rec in the mix)
Double line spacing:
Noon-3pm Trojan Soundsystem  (LIVE)
3pm � 4pm Har Mar Superstar�s Desert Island Disco (pre rec)
4pm � 8pm Mountain of One
8pm � 11pm Shortwave Set (pre rec)
11pm � Midnight Inflagranti (pre rec in the mix)';
		
		$result = $this->Cleaner->clean('<p>4pm - 8pm Mountain of One</p>');
		$this->assertEqual($result, '<p>4pm - 8pm Mountain of One</p>');

		$result = $this->Cleaner->clean($text);
		$this->assertEqual($result, $text);

		$text2 = '<p>Noon-3pm Trojan Soundsystem&nbsp; (LIVE)<br />3pm &ndash; 4pm Har Mar Superstar&rsquo;s Desert Island Disco (pre rec)<br />4pm &ndash; 8pm Mountain of One<br />8pm &ndash; 11pm Shortwave Set (pre rec)<br />11pm &ndash; Midnight Inflagranti (pre rec in the mix)<br />Double line spacing:<br />Noon-3pm Trojan Soundsystem&nbsp; (LIVE)<br />3pm &ndash; 4pm Har Mar Superstar&rsquo;s Desert Island Disco (pre rec)<br />4pm &ndash; 8pm Mountain of One<br />8pm &ndash; 11pm Shortwave Set (pre rec)</p>
';
		
		$result = $this->Cleaner->clean($text2);
		$this->assertEqual($result, $text2);
		return ;
	}
	
	
	function tearDown() {
		unset($this->Helper);
		ClassRegistry::flush();
	}
}
?>