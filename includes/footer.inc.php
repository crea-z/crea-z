        </section>
            <footer>
                <article id="foot1">
                    <?php 
					if(empty($foot1)) 
					{ ?> 
						<p>blablabla</p>
					<?php 
					} 
					else 
					{ 
						echo $foot1;
					}?>
                </article>
                <article id="foot2">
                   <?php 
					if(empty($foot2)) 
					{ ?> 
						<p>blablabla</p>
					<?php 
					} 
					else 
					{ 
						echo $foot2 ;
					}?>
                </article>
                <article id="foot3">
                    <?php 
					if(empty($foot3)) 
					{ ?> 
						<p>blablabla</p>
					<?php 
					} 
					else 
					{ 
						echo $foot3 ;
					}?>
                </article>
            </footer>
    </section>
</section>
</body>
</html>