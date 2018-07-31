	    </div>
	    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.1/jquery.min.js"></script>
	    <script>window.jQuery || document.write('<script src="assets/js/vendor/jquery-1.10.1.min.js"><\/script>')</script>
	    <? 
	        //Display all other CSS files
	        $this->html->display_links();
	        
	        //If facebook is loaded, initiate the FB JS 
	        if(isset($this->facebook)){
	            $this->load->view('facebook/js');   
	        }
	    ?>
	    <script>
	        var _gaq=[['_setAccount','UA-XXXXX-X'],['_trackPageview']];
	        (function(d,t){var g=d.createElement(t),s=d.getElementsByTagName(t)[0];
	        g.src=('https:'==location.protocol?'//ssl':'//www')+'.google-analytics.com/ga.js';
	        s.parentNode.insertBefore(g,s)}(document,'script'));
	    </script>
    </body>
</html>