<?php
// print loader's CSS code 

function ag_loaders_switch() {
	include_once(AG_DIR .'/functions.php');
	$color = get_option('ag_loader_color', '#888888');
	
	switch(get_option('ag_loader', 'default')) {
		
		/* media grid loader */
		case 'default':
		default:
			?>
            .agl_1, .agl_2, .agl_3, .agl_4 {
                background-color: <?php echo $color ?>;
                width: 12px;
                height: 12px;
                position: absolute;
                top: 0;
                left: 0;
                
                -webkit-transform-origin: 	0 50%;
                -ms-transform-origin: 		0 50%;
                transform-origin: 			0 50%;	
                
                -webkit-animation: 	ag_loader 1.7s infinite ease-in-out;
                animation: 			ag_loader 1.7s infinite ease-in-out;
                
                -webkit-transform: 	rotateX(90deg);
                -ms-transform: 		rotateX(90deg);
                transform: 			rotateX(90deg);	
            }
            .agl_2 {
                top: 0;
                left: 14px;
                -webkit-animation-delay: 0.2s;
                animation-delay: 0.2s;
            }
            .agl_3 {
                top: 14px;
                left: 14px;
                -webkit-animation-delay: 0.4s;
                animation-delay: 0.4s;
            }
            .agl_4 {
                top: 14px;
                left: 0px;
                -webkit-animation-delay: 0.6s;
                animation-delay: 0.6s;
            }
            @-webkit-keyframes ag_loader {
                20%, 80%, 100% {-webkit-transform: rotateX(90deg);}
                40%, 60% {-webkit-transform: rotateX(0deg);}
            }
            @keyframes ag_loader {
                20%, 80%, 100% {transform: rotateX(90deg);}
                40%, 60% {transform: rotateX(0deg);}
            }
            <?php
			break;
		
			
			
		/* rotating square */
		case 'rotating_square':
			?>
			.ag_loader {
                background-color: <?php echo $color ?>;
              
                -webkit-animation: ag-rotateplane 1.2s infinite ease-in-out;
                animation: ag-rotateplane 1.2s infinite ease-in-out;
            }
            .ag_grid_wrap .ag_loader {
                width: 32px;
                height: 32px;	
                margin-top: -16px;
                margin-left: -16px;
            }
            @-webkit-keyframes ag-rotateplane {
                0% 	{-webkit-transform: perspective(120px);}
                50% 	{-webkit-transform: perspective(120px) rotateY(180deg);}
                100% 	{-webkit-transform: perspective(120px) rotateY(180deg)  rotateX(180deg);}
            }
            @keyframes ag-rotateplane {
                0%	{transform: perspective(120px) rotateX(0deg) rotateY(0deg);} 
                50%	{transform: perspective(120px) rotateX(-180.1deg) rotateY(0deg);} 
                100%	{transform: perspective(120px) rotateX(-180deg) rotateY(-179.9deg);}
            }
			<?php
			break;
			
			
			
		/* overlapping circles */
		case 'overlapping_circles':
			?>
            .ag_loader div {
                background-color: <?php echo $color ?>;
            }
			.ag_loader {
                width: 32px;
                height: 32px;	
                margin-top: -16px;
                margin-left: -16px;
            }
            .agl_1, .agl_2 {
                width: 100%;
                height: 100%;
                border-radius: 50%;
                opacity: 0.6;
                position: absolute;
                top: 0;
                left: 0;
                
                -webkit-animation: ag-bounce 1.8s infinite ease-in-out;
                animation: ag-bounce 1.8s infinite ease-in-out;
            }
            .agl_2 {
                -webkit-animation-delay: -1.0s;
                animation-delay: -1.0s;
            }
            
            @-webkit-keyframes ag-bounce {
                0%, 100% {-webkit-transform: scale(0.0);}
                50% {-webkit-transform: scale(1.0);}
            }
            @keyframes ag-bounce {
                0%, 100% {transform: scale(0.0);} 
                50% {transform: scale(1.0);}
            }
			<?php
			break;
			
			
			
		/* stretching rectangles */
		case 'stretch_rect':
			?>
			.ag_loader div {
                background-color: <?php echo $color ?>;
            }
            .agl_1, .agl_2, .agl_3 {
                height: 100%;
                width: 6px;
                display: inline-block;
                position: absolute;
                
                -webkit-animation: ag-stretchdelay 1.1s infinite ease-in-out;
                animation: ag-stretchdelay 1.1s infinite ease-in-out;
            }
            .agl_2 {
                left: 10px;
                -webkit-animation-delay: -1s;
                animation-delay: -1s;
            }
            .agl_3 {
                right: 0;
                -webkit-animation-delay: -.9s;
                animation-delay: -.9s;
            }
            @-webkit-keyframes ag-stretchdelay {
                0%, 40%, 100% {-webkit-transform: scaleY(0.6);}  
                20% {-webkit-transform: scaleY(1.1);}
            }
            @keyframes ag-stretchdelay {
                0%, 40%, 100% {transform: scaleY(0.6);}  
                20% {transform: scaleY(1.1);}
            }
			<?php
			break;
			
			
			
		/* spin and fill square */
		case 'spin_n_fill_square':
			?>
            .ag_loader {
                border-color: <?php echo $color ?>;
            }
            .ag_loader div {
                background-color: <?php echo $color ?>;
            }
            
            .ag_loader {
                border-size: 3px;
                border-style: solid;
                
                -webkit-animation: ag_spinNfill 2.3s infinite ease;
                animation: ag_spinNfill 2.3s infinite ease;
            }
            .agl_1 {
                vertical-align: top;
                width: 100%;
                
                -webkit-animation: ag_spinNfill-inner 2.3s infinite ease-in;
                animation: ag_spinNfill-inner 2.3s infinite ease-in;
            }
            
            @-webkit-keyframes ag_spinNfill {
                0% {-webkit-transform: rotate(0deg);}
                25%, 50% {-webkit-transform: rotate(180deg);}
                75%, 100% {-webkit-transform: rotate(360deg);}
            }
            @keyframes ag_spinNfill {
                0% {transform: rotate(0deg);}
                25%, 50%  {transform: rotate(180deg);}
                75%, 100% {transform: rotate(360deg);}
            }
            @-webkit-keyframes ag_spinNfill-inner {
                0%, 25%, 100% {height: 0%;}
                50%, 75% {height: 100%;}
            }
            @keyframes ag_spinNfill-inner {
                0%, 25%, 100% {height: 0%;}
                50%, 75% {height: 100%;}
            }
			<?php
			break;
			
			
			
		/* pulsing circle */
		case 'pulsing_circle':
			?>
            .ag_loader {
                border-radius: 100%;  
                background-color: <?php echo $color ?>;
                
                -webkit-animation: ag-scaleout 1.0s infinite ease-in-out;
                animation: ag-scaleout 1.0s infinite ease-in-out;
            }
            .ag_grid_wrap .ag_loader {
                width: 36px;
                height: 36px;	
                margin-top: -18px;
                margin-left: -18px;
            }
            @-webkit-keyframes ag-scaleout {
                0% { -webkit-transform: scale(0);}
                100% {
                  -webkit-transform: scale(1.0);
                  opacity: 0;
                }
            }
            @keyframes ag-scaleout {
                0% {transform: scale(0);} 
                100% {
                  transform: scale(1.0);
                  opacity: 0;
                }
            }
			<?php
			break;	
			
			
			
		/* spinning dots */
		case 'spinning_dots':
			?>
            .ag_loader div {
                background-color: <?php echo $color ?>;
            }
            .ag_loader {
              text-align: center;
              -webkit-animation: ag-rotate 1.6s infinite linear;
              animation: ag-rotate 1.6s infinite linear;
            }
            .ag_grid_wrap .ag_loader {
                width: 36px;
                height: 36px;	
                margin-top: -18px;
                margin-left: -18px;
            }
            .agl_1, .agl_2 {
                width: 57%;
                height: 57%;
                display: inline-block;
                position: absolute;
                top: 0;
                border-radius: 100%;
                
                -webkit-animation: ag-bounce 1.6s infinite ease-in-out;
                animation: ag-bounce 1.6s infinite ease-in-out;
            }
            .agl_2 {
                top: auto;
                bottom: 0;
                -webkit-animation-delay: -.8s;
                animation-delay: -.8s;
            }
            @-webkit-keyframes ag-rotate {
                0% { -webkit-transform: rotate(0deg) }
                100% { -webkit-transform: rotate(360deg) }
            }
            @keyframes ag-rotate { 
                0% { transform: rotate(0deg); -webkit-transform: rotate(0deg) }
                100% { transform: rotate(360deg); -webkit-transform: rotate(360deg) }
            }
            @-webkit-keyframes ag-bounce {
                0%, 100% {-webkit-transform: scale(0);}
                50% {-webkit-transform: scale(1);}
            }
            @keyframes ag-bounce {
                0%, 100% {transform: scale(0.0);} 
                50% {transform: scale(1.0);}
            }
			<?php
			break;	
			
			
			
		/* appearing cubes */
		case 'appearing_cubes':
			?>
            .ag_loader div {
                background-color: <?php echo $color ?>;
            }
            .agl_1, .agl_2, .agl_3, .agl_4 {
                width: 50%;
                height: 50%;
                float: left;
                
                -webkit-animation:	ag-cubeGridScaleDelay 1.3s infinite ease-in-out;
                animation: 			ag-cubeGridScaleDelay 1.3s infinite ease-in-out; 
            }
            .ag_grid_wrap .ag_loader {
                width: 36px;
                height: 36px;	
                margin-top: -18px;
                margin-left: -18px;
            }
            .agl_1, .agl_4 {
              	-webkit-animation-delay: .1s;
                      animation-delay: .1s; 
            }
            .agl_2 {
              	-webkit-animation-delay: .2s;
                		animation-delay: .2s; 
            }
            @-webkit-keyframes ag-cubeGridScaleDelay {
                0%, 70%, 100% {
                  -webkit-transform: scale3D(1, 1, 1);
                          transform: scale3D(1, 1, 1);
                } 35% {
                  -webkit-transform: scale3D(0, 0, 1);
                          transform: scale3D(0, 0, 1); 
                }
            }
            @keyframes ag-cubeGridScaleDelay {
                0%, 70%, 100% {
                  -webkit-transform: scale3D(1, 1, 1);
                          transform: scale3D(1, 1, 1);
                } 35% {
                  -webkit-transform: scale3D(0, 0, 1);
                          transform: scale3D(0, 0, 1);
                } 
            }
			<?php
			break;
			
			
			
		/* folding cube */
		case 'folding_cube':
			?>
            .ag_loader div:before {
                background-color: <?php echo $color ?>;
            }
            .ag_loader {
              -webkit-transform: rotateZ(45deg);
                      transform: rotateZ(45deg);
            }
            .agl_1, .agl_2, .agl_3, .agl_4 {
              float: left;
              width: 50%;
              height: 50%;
              position: relative;
              -webkit-transform: scale(1.1);
                  -ms-transform: scale(1.1);
                      transform: scale(1.1); 
            }
            .ag_loader div:before {
              content: '';
              position: absolute;
              top: 0;
              left: 0;
              width: 100%;
              height: 100%;
              -webkit-animation: ag-foldCubeAngle 2.3s infinite linear both;
                      animation: ag-foldCubeAngle 2.3s infinite linear both;
                      
              -webkit-transform-origin: 100% 100%;
                  -ms-transform-origin: 100% 100%;
                      transform-origin: 100% 100%;
            }
            .agl_2 {
              -webkit-transform: scale(1.1) rotateZ(90deg);
                      transform: scale(1.1) rotateZ(90deg);
            }
            .agl_3 {
              -webkit-transform: scale(1.1) rotateZ(270deg);
                      transform: scale(1.1) rotateZ(270deg);
            }
            .agl_4 {
              -webkit-transform: scale(1.1) rotateZ(180deg);
                      transform: scale(1.1) rotateZ(180deg);
            }
            .ag_loader .agl_2:before {
              -webkit-animation-delay: 0.3s;
                      animation-delay: 0.3s;
            }
            .ag_loader .agl_3:before {
              -webkit-animation-delay: 0.9s;
                      animation-delay: 0.9s;
            }
            .ag_loader .agl_4:before {
              -webkit-animation-delay: 0.6s;
                      animation-delay: 0.6s; 
            }
            @-webkit-keyframes ag-foldCubeAngle {
              0%, 10% {
              	-webkit-transform: perspective(140px) rotateX(-180deg);
                opacity: 0; 
              } 
              25%, 75% {
                -webkit-transform: perspective(140px) rotateX(0deg);
                opacity: 1; 
              } 
              90%, 100% {
                -webkit-transform: perspective(140px) rotateY(180deg);
                opacity: 0; 
              } 
            }
            @keyframes ag-foldCubeAngle {
              0%, 10% {
                transform: perspective(140px) rotateX(-180deg);
                opacity: 0; 
              } 
              25%, 75% {
                transform: perspective(140px) rotateX(0deg);
                opacity: 1; 
              } 
              90%, 100% {
                transform: perspective(140px) rotateY(180deg);
                opacity: 0; 
              }
            }
			<?php
			break;
			
			
			
		/* old-style circles spinner */
		case 'old_style_spinner':
			?>
            .ag_loader div:before {
                color: <?php echo $color ?>;
            }
			.ag_loader {            	
                font-size: 20px;
                border-radius: 50%;
  
                -webkit-animation: ag-circles-spinner 1.3s infinite linear;
                animation: ag-circles-spinner 1.3s infinite linear;
                
                -webkit-transform: 	scale(0.28) translateZ(0);
                transform: 			scale(0.28) translateZ(0);	
            }
            @-webkit-keyframes ag-circles-spinner {
              0%,
              100%	{box-shadow: 0 -3em 0 0.2em, 2em -2em 0 0em, 3em 0 0 -1em, 2em 2em 0 -1em, 0 3em 0 -1em, -2em 2em 0 -1em, -3em 0 0 -1em, -2em -2em 0 0;}
              12.5% {box-shadow: 0 -3em 0 0, 2em -2em 0 0.2em, 3em 0 0 0, 2em 2em 0 -1em, 0 3em 0 -1em, -2em 2em 0 -1em, -3em 0 0 -1em, -2em -2em 0 -1em;}
              25%	{box-shadow: 0 -3em 0 -0.5em, 2em -2em 0 0, 3em 0 0 0.2em, 2em 2em 0 0, 0 3em 0 -1em, -2em 2em 0 -1em, -3em 0 0 -1em, -2em -2em 0 -1em;}
              37.5% {box-shadow: 0 -3em 0 -1em, 2em -2em 0 -1em, 3em 0em 0 0, 2em 2em 0 0.2em, 0 3em 0 0em, -2em 2em 0 -1em, -3em 0em 0 -1em, -2em -2em 0 -1em;}
              50%	{box-shadow: 0 -3em 0 -1em, 2em -2em 0 -1em, 3em 0 0 -1em, 2em 2em 0 0em, 0 3em 0 0.2em, -2em 2em 0 0, -3em 0em 0 -1em, -2em -2em 0 -1em;}
              62.5% {box-shadow: 0 -3em 0 -1em, 2em -2em 0 -1em, 3em 0 0 -1em, 2em 2em 0 -1em, 0 3em 0 0, -2em 2em 0 0.2em, -3em 0 0 0, -2em -2em 0 -1em;}
              75% 	{box-shadow: 0em -3em 0 -1em, 2em -2em 0 -1em, 3em 0em 0 -1em, 2em 2em 0 -1em, 0 3em 0 -1em, -2em 2em 0 0, -3em 0em 0 0.2em, -2em -2em 0 0;}
              87.5% {box-shadow: 0em -3em 0 0, 2em -2em 0 -1em, 3em 0 0 -1em, 2em 2em 0 -1em, 0 3em 0 -1em, -2em 2em 0 0, -3em 0em 0 0, -2em -2em 0 0.2em;}
            }
            @keyframes ag-circles-spinner {
              0%,
              100% 	{box-shadow: 0 -3em 0 0.2em, 2em -2em 0 0em, 3em 0 0 -1em, 2em 2em 0 -1em, 0 3em 0 -1em, -2em 2em 0 -1em, -3em 0 0 -1em, -2em -2em 0 0;}
              12.5% {box-shadow: 0 -3em 0 0, 2em -2em 0 0.2em, 3em 0 0 0, 2em 2em 0 -1em, 0 3em 0 -1em, -2em 2em 0 -1em, -3em 0 0 -1em, -2em -2em 0 -1em;}
              25% 	{box-shadow: 0 -3em 0 -0.5em, 2em -2em 0 0, 3em 0 0 0.2em, 2em 2em 0 0, 0 3em 0 -1em, -2em 2em 0 -1em, -3em 0 0 -1em, -2em -2em 0 -1em;}
              37.5% {box-shadow: 0 -3em 0 -1em, 2em -2em 0 -1em, 3em 0em 0 0, 2em 2em 0 0.2em, 0 3em 0 0em, -2em 2em 0 -1em, -3em 0em 0 -1em, -2em -2em 0 -1em;}
              50% 	{box-shadow: 0 -3em 0 -1em, 2em -2em 0 -1em, 3em 0 0 -1em, 2em 2em 0 0em, 0 3em 0 0.2em, -2em 2em 0 0, -3em 0em 0 -1em, -2em -2em 0 -1em;}
              62.5% {box-shadow: 0 -3em 0 -1em, 2em -2em 0 -1em, 3em 0 0 -1em, 2em 2em 0 -1em, 0 3em 0 0, -2em 2em 0 0.2em, -3em 0 0 0, -2em -2em 0 -1em;}
              75% 	{box-shadow: 0em -3em 0 -1em, 2em -2em 0 -1em, 3em 0em 0 -1em, 2em 2em 0 -1em, 0 3em 0 -1em, -2em 2em 0 0, -3em 0em 0 0.2em, -2em -2em 0 0;}
              87.5% {box-shadow: 0em -3em 0 0, 2em -2em 0 -1em, 3em 0 0 -1em, 2em 2em 0 -1em, 0 3em 0 -1em, -2em 2em 0 0, -3em 0em 0 0, -2em -2em 0 0.2em;}
            }
			<?php
			break;
			
			
			
		/* minimal spinner */
		case 'minimal_spinner':
			?>
            .ag_loader .agl_1 {
            	border-color: <?php echo ag_hex2rgba($color, '.25').' '.ag_hex2rgba($color, '.25').' '.$color ?>;
            }
			.ag_loader {
                width: 34px;
                height: 34px;
                margin-top: -17px;
                margin-left: -17px;	
            }
            .agl_1,
            .agl_1:after {
                border-radius: 50%;
                box-sizing: border-box !important;	
                height: 100%;
            }
            .agl_1 {
                background: none !important;
                font-size: 10px;
                border-size: 6px;
                border-style: solid;
                
                -webkit-animation: 	ag_minimal_spinner 1.05s infinite linear;
                animation: 			ag_minimal_spinner 1.05s infinite linear;
            }
            @-webkit-keyframes ag_minimal_spinner {
                0% {-webkit-transform: rotate(0deg);}
                100% {-webkit-transform: rotate(360deg);}
            }
            @keyframes ag_minimal_spinner {
                0% {transform: rotate(0deg);}
                100% {transform: rotate(360deg);}
            }
			<?php
			break;
			
			
			
		/* spotify-like spinner */
		case 'spotify_like':
			?>
            .agl_1 {
                background: none !important;
                border-radius: 50%;
                font-size: 5px;
                height: 28%;
                margin-left: 36%;
                margin-top: 36%;
                width: 28%;
            
                -webkit-animation: 	ag_spotify .9s infinite ease;
                animation: 			ag_spotify .9s infinite ease;
            }
            
            @-webkit-keyframes ag_spotify {
              0%,
              100% {
                box-shadow: 0em -2.6em 0em 0em <?php echo $color ?>, 1.8em -1.8em 0 0em <?php echo ag_hex2rgba($color, '.2') ?>, 2.5em 0em 0 0em <?php echo ag_hex2rgba($color, '.2') ?>, 1.75em 1.75em 0 0em <?php echo ag_hex2rgba($color, '.2') ?>, 0em 2.5em 0 0em <?php echo ag_hex2rgba($color, '.2') ?>, -1.8em 1.8em 0 0em <?php echo ag_hex2rgba($color, '.2') ?>, -2.6em 0em 0 0em <?php echo ag_hex2rgba($color, '.5') ?>, -1.8em -1.8em 0 0em <?php echo ag_hex2rgba($color, '.7') ?>;
              }
              12.5% {
                box-shadow: 0em -2.6em 0em 0em <?php echo ag_hex2rgba($color, '.7') ?>, 1.8em -1.8em 0 0em <?php echo $color ?>, 2.5em 0em 0 0em <?php echo ag_hex2rgba($color, '.2') ?>, 1.75em 1.75em 0 0em <?php echo ag_hex2rgba($color, '.2') ?>, 0em 2.5em 0 0em <?php echo ag_hex2rgba($color, '.2') ?>, -1.8em 1.8em 0 0em <?php echo ag_hex2rgba($color, '.2') ?>, -2.6em 0em 0 0em <?php echo ag_hex2rgba($color, '.2') ?>, -1.8em -1.8em 0 0em <?php echo ag_hex2rgba($color, '.5') ?>;
              }
              25% {
                box-shadow: 0em -2.6em 0em 0em <?php echo ag_hex2rgba($color, '.5') ?>, 1.8em -1.8em 0 0em <?php echo ag_hex2rgba($color, '.7') ?>, 2.5em 0em 0 0em <?php echo $color ?>, 1.75em 1.75em 0 0em <?php echo ag_hex2rgba($color, '.2') ?>, 0em 2.5em 0 0em <?php echo ag_hex2rgba($color, '.2') ?>, -1.8em 1.8em 0 0em <?php echo ag_hex2rgba($color, '.2') ?>, -2.6em 0em 0 0em <?php echo ag_hex2rgba($color, '.2') ?>, -1.8em -1.8em 0 0em <?php echo ag_hex2rgba($color, '.2') ?>;
              }
              37.5% {
                box-shadow: 0em -2.6em 0em 0em <?php echo ag_hex2rgba($color, '.2') ?>, 1.8em -1.8em 0 0em <?php echo ag_hex2rgba($color, '.5') ?>, 2.5em 0em 0 0em <?php echo ag_hex2rgba($color, '.7') ?>, 1.75em 1.75em 0 0em <?php echo $color ?>, 0em 2.5em 0 0em <?php echo ag_hex2rgba($color, '.2') ?>, -1.8em 1.8em 0 0em <?php echo ag_hex2rgba($color, '.2') ?>, -2.6em 0em 0 0em <?php echo ag_hex2rgba($color, '.2') ?>, -1.8em -1.8em 0 0em <?php echo ag_hex2rgba($color, '.2') ?>;
              }
              50% {
                box-shadow: 0em -2.6em 0em 0em <?php echo ag_hex2rgba($color, '.2') ?>, 1.8em -1.8em 0 0em <?php echo ag_hex2rgba($color, '.2') ?>, 2.5em 0em 0 0em <?php echo ag_hex2rgba($color, '.5') ?>, 1.75em 1.75em 0 0em <?php echo ag_hex2rgba($color, '.7') ?>, 0em 2.5em 0 0em <?php echo $color ?>, -1.8em 1.8em 0 0em <?php echo ag_hex2rgba($color, '.2') ?>, -2.6em 0em 0 0em <?php echo ag_hex2rgba($color, '.2') ?>, -1.8em -1.8em 0 0em <?php echo ag_hex2rgba($color, '.2') ?>;
              }
              62.5% {
                box-shadow: 0em -2.6em 0em 0em <?php echo ag_hex2rgba($color, '.2') ?>, 1.8em -1.8em 0 0em <?php echo ag_hex2rgba($color, '.2') ?>, 2.5em 0em 0 0em <?php echo ag_hex2rgba($color, '.2') ?>, 1.75em 1.75em 0 0em <?php echo ag_hex2rgba($color, '.5') ?>, 0em 2.5em 0 0em <?php echo ag_hex2rgba($color, '.7') ?>, -1.8em 1.8em 0 0em <?php echo $color ?>, -2.6em 0em 0 0em <?php echo ag_hex2rgba($color, '.2') ?>, -1.8em -1.8em 0 0em <?php echo ag_hex2rgba($color, '.2') ?>;
              }
              75% {
                box-shadow: 0em -2.6em 0em 0em <?php echo ag_hex2rgba($color, '.2') ?>, 1.8em -1.8em 0 0em <?php echo ag_hex2rgba($color, '.2') ?>, 2.5em 0em 0 0em <?php echo ag_hex2rgba($color, '.2') ?>, 1.75em 1.75em 0 0em <?php echo ag_hex2rgba($color, '.2') ?>, 0em 2.5em 0 0em <?php echo ag_hex2rgba($color, '.5') ?>, -1.8em 1.8em 0 0em <?php echo ag_hex2rgba($color, '.7') ?>, -2.6em 0em 0 0em <?php echo $color ?>, -1.8em -1.8em 0 0em <?php echo ag_hex2rgba($color, '.2') ?>;
              }
              87.5% {
                box-shadow: 0em -2.6em 0em 0em <?php echo ag_hex2rgba($color, '.2') ?>, 1.8em -1.8em 0 0em <?php echo ag_hex2rgba($color, '.2') ?>, 2.5em 0em 0 0em <?php echo ag_hex2rgba($color, '.2') ?>, 1.75em 1.75em 0 0em <?php echo ag_hex2rgba($color, '.2') ?>, 0em 2.5em 0 0em <?php echo ag_hex2rgba($color, '.2') ?>, -1.8em 1.8em 0 0em <?php echo ag_hex2rgba($color, '.5') ?>, -2.6em 0em 0 0em <?php echo ag_hex2rgba($color, '.7') ?>, -1.8em -1.8em 0 0em <?php echo $color ?>;
              }
            }
            @keyframes ag_spotify {
              0%,
              100% {
                box-shadow: 0em -2.6em 0em 0em <?php echo $color ?>, 1.8em -1.8em 0 0em <?php echo ag_hex2rgba($color, '.2') ?>, 2.5em 0em 0 0em <?php echo ag_hex2rgba($color, '.2') ?>, 1.75em 1.75em 0 0em <?php echo ag_hex2rgba($color, '.2') ?>, 0em 2.5em 0 0em <?php echo ag_hex2rgba($color, '.2') ?>, -1.8em 1.8em 0 0em <?php echo ag_hex2rgba($color, '.2') ?>, -2.6em 0em 0 0em <?php echo ag_hex2rgba($color, '.5') ?>, -1.8em -1.8em 0 0em <?php echo ag_hex2rgba($color, '.7') ?>;
              }
              12.5% {
                box-shadow: 0em -2.6em 0em 0em <?php echo ag_hex2rgba($color, '.7') ?>, 1.8em -1.8em 0 0em <?php echo $color ?>, 2.5em 0em 0 0em <?php echo ag_hex2rgba($color, '.2') ?>, 1.75em 1.75em 0 0em <?php echo ag_hex2rgba($color, '.2') ?>, 0em 2.5em 0 0em <?php echo ag_hex2rgba($color, '.2') ?>, -1.8em 1.8em 0 0em <?php echo ag_hex2rgba($color, '.2') ?>, -2.6em 0em 0 0em <?php echo ag_hex2rgba($color, '.2') ?>, -1.8em -1.8em 0 0em <?php echo ag_hex2rgba($color, '.5') ?>;
              }
              25% {
                box-shadow: 0em -2.6em 0em 0em <?php echo ag_hex2rgba($color, '.5') ?>, 1.8em -1.8em 0 0em <?php echo ag_hex2rgba($color, '.7') ?>, 2.5em 0em 0 0em <?php echo $color ?>, 1.75em 1.75em 0 0em <?php echo ag_hex2rgba($color, '.2') ?>, 0em 2.5em 0 0em <?php echo ag_hex2rgba($color, '.2') ?>, -1.8em 1.8em 0 0em <?php echo ag_hex2rgba($color, '.2') ?>, -2.6em 0em 0 0em <?php echo ag_hex2rgba($color, '.2') ?>, -1.8em -1.8em 0 0em <?php echo ag_hex2rgba($color, '.2') ?>;
              }
              37.5% {
                box-shadow: 0em -2.6em 0em 0em <?php echo ag_hex2rgba($color, '.2') ?>, 1.8em -1.8em 0 0em <?php echo ag_hex2rgba($color, '.5') ?>, 2.5em 0em 0 0em <?php echo ag_hex2rgba($color, '.7') ?>, 1.75em 1.75em 0 0em <?php echo $color ?>, 0em 2.5em 0 0em <?php echo ag_hex2rgba($color, '.2') ?>, -1.8em 1.8em 0 0em <?php echo ag_hex2rgba($color, '.2') ?>, -2.6em 0em 0 0em <?php echo ag_hex2rgba($color, '.2') ?>, -1.8em -1.8em 0 0em <?php echo ag_hex2rgba($color, '.2') ?>;
              }
              50% {
                box-shadow: 0em -2.6em 0em 0em <?php echo ag_hex2rgba($color, '.2') ?>, 1.8em -1.8em 0 0em <?php echo ag_hex2rgba($color, '.2') ?>, 2.5em 0em 0 0em <?php echo ag_hex2rgba($color, '.5') ?>, 1.75em 1.75em 0 0em <?php echo ag_hex2rgba($color, '.7') ?>, 0em 2.5em 0 0em <?php echo $color ?>, -1.8em 1.8em 0 0em <?php echo ag_hex2rgba($color, '.2') ?>, -2.6em 0em 0 0em <?php echo ag_hex2rgba($color, '.2') ?>, -1.8em -1.8em 0 0em <?php echo ag_hex2rgba($color, '.2') ?>;
              }
              62.5% {
                box-shadow: 0em -2.6em 0em 0em <?php echo ag_hex2rgba($color, '.2') ?>, 1.8em -1.8em 0 0em <?php echo ag_hex2rgba($color, '.2') ?>, 2.5em 0em 0 0em <?php echo ag_hex2rgba($color, '.2') ?>, 1.75em 1.75em 0 0em <?php echo ag_hex2rgba($color, '.5') ?>, 0em 2.5em 0 0em <?php echo ag_hex2rgba($color, '.7') ?>, -1.8em 1.8em 0 0em <?php echo $color ?>, -2.6em 0em 0 0em <?php echo ag_hex2rgba($color, '.2') ?>, -1.8em -1.8em 0 0em <?php echo ag_hex2rgba($color, '.2') ?>;
              }
              75% {
                box-shadow: 0em -2.6em 0em 0em <?php echo ag_hex2rgba($color, '.2') ?>, 1.8em -1.8em 0 0em <?php echo ag_hex2rgba($color, '.2') ?>, 2.5em 0em 0 0em <?php echo ag_hex2rgba($color, '.2') ?>, 1.75em 1.75em 0 0em <?php echo ag_hex2rgba($color, '.2') ?>, 0em 2.5em 0 0em <?php echo ag_hex2rgba($color, '.5') ?>, -1.8em 1.8em 0 0em <?php echo ag_hex2rgba($color, '.7') ?>, -2.6em 0em 0 0em <?php echo $color ?>, -1.8em -1.8em 0 0em <?php echo ag_hex2rgba($color, '.2') ?>;
              }
              87.5% {
                box-shadow: 0em -2.6em 0em 0em <?php echo ag_hex2rgba($color, '.2') ?>, 1.8em -1.8em 0 0em <?php echo ag_hex2rgba($color, '.2') ?>, 2.5em 0em 0 0em <?php echo ag_hex2rgba($color, '.2') ?>, 1.75em 1.75em 0 0em <?php echo ag_hex2rgba($color, '.2') ?>, 0em 2.5em 0 0em <?php echo ag_hex2rgba($color, '.2') ?>, -1.8em 1.8em 0 0em <?php echo ag_hex2rgba($color, '.5') ?>, -2.6em 0em 0 0em <?php echo ag_hex2rgba($color, '.7') ?>, -1.8em -1.8em 0 0em <?php echo $color ?>;
              }
            }
			<?php
			break;
		
		
		
		/* minimal spinner */
		case 'vortex':
			?>
            .agl_1 {
                background: none !important;
                border-radius: 50%;
                font-size: 3px;
                height: 70%;
                margin-left: 15%;
                margin-top: 15%;
                width: 70%;
              
                -webkit-animation:	ag_vortex .45s infinite linear;
                animation: 			ag_vortex .45s infinite linear;
            }

            @-webkit-keyframes ag_vortex {
              0%,
              100% {
                box-shadow: 0em -2.6em 0em 0em <?php echo $color ?>, 1.8em -1.8em 0 0em <?php echo ag_hex2rgba($color, '.2') ?>, 2.5em 0em 0 0em <?php echo ag_hex2rgba($color, '.2') ?>, 1.75em 1.75em 0 0em <?php echo ag_hex2rgba($color, '.2') ?>, 0em 2.5em 0 0em <?php echo ag_hex2rgba($color, '.2') ?>, -1.8em 1.8em 0 0em <?php echo ag_hex2rgba($color, '.2') ?>, -2.6em 0em 0 0em <?php echo ag_hex2rgba($color, '.5') ?>, -1.8em -1.8em 0 0em <?php echo ag_hex2rgba($color, '.7') ?>;
              }
              12.5% {
                box-shadow: 0em -2.6em 0em 0em <?php echo ag_hex2rgba($color, '.7') ?>, 1.8em -1.8em 0 0em <?php echo $color ?>, 2.5em 0em 0 0em <?php echo ag_hex2rgba($color, '.2') ?>, 1.75em 1.75em 0 0em <?php echo ag_hex2rgba($color, '.2') ?>, 0em 2.5em 0 0em <?php echo ag_hex2rgba($color, '.2') ?>, -1.8em 1.8em 0 0em <?php echo ag_hex2rgba($color, '.2') ?>, -2.6em 0em 0 0em <?php echo ag_hex2rgba($color, '.2') ?>, -1.8em -1.8em 0 0em <?php echo ag_hex2rgba($color, '.5') ?>;
              }
              25% {
                box-shadow: 0em -2.6em 0em 0em <?php echo ag_hex2rgba($color, '.5') ?>, 1.8em -1.8em 0 0em <?php echo ag_hex2rgba($color, '.7') ?>, 2.5em 0em 0 0em <?php echo $color ?>, 1.75em 1.75em 0 0em <?php echo ag_hex2rgba($color, '.2') ?>, 0em 2.5em 0 0em <?php echo ag_hex2rgba($color, '.2') ?>, -1.8em 1.8em 0 0em <?php echo ag_hex2rgba($color, '.2') ?>, -2.6em 0em 0 0em <?php echo ag_hex2rgba($color, '.2') ?>, -1.8em -1.8em 0 0em <?php echo ag_hex2rgba($color, '.2') ?>;
              }
              37.5% {
                box-shadow: 0em -2.6em 0em 0em <?php echo ag_hex2rgba($color, '.2') ?>, 1.8em -1.8em 0 0em <?php echo ag_hex2rgba($color, '.5') ?>, 2.5em 0em 0 0em <?php echo ag_hex2rgba($color, '.7') ?>, 1.75em 1.75em 0 0em <?php echo $color ?>, 0em 2.5em 0 0em <?php echo ag_hex2rgba($color, '.2') ?>, -1.8em 1.8em 0 0em <?php echo ag_hex2rgba($color, '.2') ?>, -2.6em 0em 0 0em <?php echo ag_hex2rgba($color, '.2') ?>, -1.8em -1.8em 0 0em <?php echo ag_hex2rgba($color, '.2') ?>;
              }
              50% {
                box-shadow: 0em -2.6em 0em 0em <?php echo ag_hex2rgba($color, '.2') ?>, 1.8em -1.8em 0 0em <?php echo ag_hex2rgba($color, '.2') ?>, 2.5em 0em 0 0em <?php echo ag_hex2rgba($color, '.5') ?>, 1.75em 1.75em 0 0em <?php echo ag_hex2rgba($color, '.7') ?>, 0em 2.5em 0 0em <?php echo $color ?>, -1.8em 1.8em 0 0em <?php echo ag_hex2rgba($color, '.2') ?>, -2.6em 0em 0 0em <?php echo ag_hex2rgba($color, '.2') ?>, -1.8em -1.8em 0 0em <?php echo ag_hex2rgba($color, '.2') ?>;
              }
              62.5% {
                box-shadow: 0em -2.6em 0em 0em <?php echo ag_hex2rgba($color, '.2') ?>, 1.8em -1.8em 0 0em <?php echo ag_hex2rgba($color, '.2') ?>, 2.5em 0em 0 0em <?php echo ag_hex2rgba($color, '.2') ?>, 1.75em 1.75em 0 0em <?php echo ag_hex2rgba($color, '.5') ?>, 0em 2.5em 0 0em <?php echo ag_hex2rgba($color, '.7') ?>, -1.8em 1.8em 0 0em <?php echo $color ?>, -2.6em 0em 0 0em <?php echo ag_hex2rgba($color, '.2') ?>, -1.8em -1.8em 0 0em <?php echo ag_hex2rgba($color, '.2') ?>;
              }
              75% {
                box-shadow: 0em -2.6em 0em 0em <?php echo ag_hex2rgba($color, '.2') ?>, 1.8em -1.8em 0 0em <?php echo ag_hex2rgba($color, '.2') ?>, 2.5em 0em 0 0em <?php echo ag_hex2rgba($color, '.2') ?>, 1.75em 1.75em 0 0em <?php echo ag_hex2rgba($color, '.2') ?>, 0em 2.5em 0 0em <?php echo ag_hex2rgba($color, '.5') ?>, -1.8em 1.8em 0 0em <?php echo ag_hex2rgba($color, '.7') ?>, -2.6em 0em 0 0em <?php echo $color ?>, -1.8em -1.8em 0 0em <?php echo ag_hex2rgba($color, '.2') ?>;
              }
              87.5% {
                box-shadow: 0em -2.6em 0em 0em <?php echo ag_hex2rgba($color, '.2') ?>, 1.8em -1.8em 0 0em <?php echo ag_hex2rgba($color, '.2') ?>, 2.5em 0em 0 0em <?php echo ag_hex2rgba($color, '.2') ?>, 1.75em 1.75em 0 0em <?php echo ag_hex2rgba($color, '.2') ?>, 0em 2.5em 0 0em <?php echo ag_hex2rgba($color, '.2') ?>, -1.8em 1.8em 0 0em <?php echo ag_hex2rgba($color, '.5') ?>, -2.6em 0em 0 0em <?php echo ag_hex2rgba($color, '.7') ?>, -1.8em -1.8em 0 0em <?php echo $color ?>;
              }
            }
            @keyframes ag_vortex {
              0%,
              100% {
                box-shadow: 0em -2.6em 0em 0em <?php echo $color ?>, 1.8em -1.8em 0 0em <?php echo ag_hex2rgba($color, '.2') ?>, 2.5em 0em 0 0em <?php echo ag_hex2rgba($color, '.2') ?>, 1.75em 1.75em 0 0em <?php echo ag_hex2rgba($color, '.2') ?>, 0em 2.5em 0 0em <?php echo ag_hex2rgba($color, '.2') ?>, -1.8em 1.8em 0 0em <?php echo ag_hex2rgba($color, '.2') ?>, -2.6em 0em 0 0em <?php echo ag_hex2rgba($color, '.5') ?>, -1.8em -1.8em 0 0em <?php echo ag_hex2rgba($color, '.7') ?>;
              }
              12.5% {
                box-shadow: 0em -2.6em 0em 0em <?php echo ag_hex2rgba($color, '.7') ?>, 1.8em -1.8em 0 0em <?php echo $color ?>, 2.5em 0em 0 0em <?php echo ag_hex2rgba($color, '.2') ?>, 1.75em 1.75em 0 0em <?php echo ag_hex2rgba($color, '.2') ?>, 0em 2.5em 0 0em <?php echo ag_hex2rgba($color, '.2') ?>, -1.8em 1.8em 0 0em <?php echo ag_hex2rgba($color, '.2') ?>, -2.6em 0em 0 0em <?php echo ag_hex2rgba($color, '.2') ?>, -1.8em -1.8em 0 0em <?php echo ag_hex2rgba($color, '.5') ?>;
              }
              25% {
                box-shadow: 0em -2.6em 0em 0em <?php echo ag_hex2rgba($color, '.5') ?>, 1.8em -1.8em 0 0em <?php echo ag_hex2rgba($color, '.7') ?>, 2.5em 0em 0 0em <?php echo $color ?>, 1.75em 1.75em 0 0em <?php echo ag_hex2rgba($color, '.2') ?>, 0em 2.5em 0 0em <?php echo ag_hex2rgba($color, '.2') ?>, -1.8em 1.8em 0 0em <?php echo ag_hex2rgba($color, '.2') ?>, -2.6em 0em 0 0em <?php echo ag_hex2rgba($color, '.2') ?>, -1.8em -1.8em 0 0em <?php echo ag_hex2rgba($color, '.2') ?>;
              }
              37.5% {
                box-shadow: 0em -2.6em 0em 0em <?php echo ag_hex2rgba($color, '.2') ?>, 1.8em -1.8em 0 0em <?php echo ag_hex2rgba($color, '.5') ?>, 2.5em 0em 0 0em <?php echo ag_hex2rgba($color, '.7') ?>, 1.75em 1.75em 0 0em <?php echo $color ?>, 0em 2.5em 0 0em <?php echo ag_hex2rgba($color, '.2') ?>, -1.8em 1.8em 0 0em <?php echo ag_hex2rgba($color, '.2') ?>, -2.6em 0em 0 0em <?php echo ag_hex2rgba($color, '.2') ?>, -1.8em -1.8em 0 0em <?php echo ag_hex2rgba($color, '.2') ?>;
              }
              50% {
                box-shadow: 0em -2.6em 0em 0em <?php echo ag_hex2rgba($color, '.2') ?>, 1.8em -1.8em 0 0em <?php echo ag_hex2rgba($color, '.2') ?>, 2.5em 0em 0 0em <?php echo ag_hex2rgba($color, '.5') ?>, 1.75em 1.75em 0 0em <?php echo ag_hex2rgba($color, '.7') ?>, 0em 2.5em 0 0em <?php echo $color ?>, -1.8em 1.8em 0 0em <?php echo ag_hex2rgba($color, '.2') ?>, -2.6em 0em 0 0em <?php echo ag_hex2rgba($color, '.2') ?>, -1.8em -1.8em 0 0em <?php echo ag_hex2rgba($color, '.2') ?>;
              }
              62.5% {
                box-shadow: 0em -2.6em 0em 0em <?php echo ag_hex2rgba($color, '.2') ?>, 1.8em -1.8em 0 0em <?php echo ag_hex2rgba($color, '.2') ?>, 2.5em 0em 0 0em <?php echo ag_hex2rgba($color, '.2') ?>, 1.75em 1.75em 0 0em <?php echo ag_hex2rgba($color, '.5') ?>, 0em 2.5em 0 0em <?php echo ag_hex2rgba($color, '.7') ?>, -1.8em 1.8em 0 0em <?php echo $color ?>, -2.6em 0em 0 0em <?php echo ag_hex2rgba($color, '.2') ?>, -1.8em -1.8em 0 0em <?php echo ag_hex2rgba($color, '.2') ?>;
              }
              75% {
                box-shadow: 0em -2.6em 0em 0em <?php echo ag_hex2rgba($color, '.2') ?>, 1.8em -1.8em 0 0em <?php echo ag_hex2rgba($color, '.2') ?>, 2.5em 0em 0 0em <?php echo ag_hex2rgba($color, '.2') ?>, 1.75em 1.75em 0 0em <?php echo ag_hex2rgba($color, '.2') ?>, 0em 2.5em 0 0em <?php echo ag_hex2rgba($color, '.5') ?>, -1.8em 1.8em 0 0em <?php echo ag_hex2rgba($color, '.7') ?>, -2.6em 0em 0 0em <?php echo $color ?>, -1.8em -1.8em 0 0em <?php echo ag_hex2rgba($color, '.2') ?>;
              }
              87.5% {
                box-shadow: 0em -2.6em 0em 0em <?php echo ag_hex2rgba($color, '.2') ?>, 1.8em -1.8em 0 0em <?php echo ag_hex2rgba($color, '.2') ?>, 2.5em 0em 0 0em <?php echo ag_hex2rgba($color, '.2') ?>, 1.75em 1.75em 0 0em <?php echo ag_hex2rgba($color, '.2') ?>, 0em 2.5em 0 0em <?php echo ag_hex2rgba($color, '.2') ?>, -1.8em 1.8em 0 0em <?php echo ag_hex2rgba($color, '.5') ?>, -2.6em 0em 0 0em <?php echo ag_hex2rgba($color, '.7') ?>, -1.8em -1.8em 0 0em <?php echo $color ?>;
              }
            }
			<?php
			break;
			
			
			
		/* bubbling dots */
		case 'bubbling_dots':
			?>
            .ag_loader div {
                background-color: <?php echo $color ?>;
            }
            .ag_loader {
                -webkit-transform: scale(1.4);
                      transform: scale(1.4);
            }
            .agl_1, .agl_2, .agl_3 {
                border-radius: 35px;
                bottom: -8px;
                display: inline-block;
                height: 6px;
                margin: 0 2px 0 0;
                position: relative;
                width: 6px;
                
                -webkit-animation:	ag_bubbling ease .65s infinite alternate;	
                animation: 			ag_bubbling ease .65s infinite alternate;
            }
            .agl_2 {
                -webkit-animation-delay: 0.212s;
                animation-delay: 0.212s;
            }
            .agl_3 {
                margin-right: 0;
                -webkit-animation-delay: 0.425s;
                animation-delay: 0.425s;
            }
            @-webkit-keyframes ag_bubbling {
                0% 		{-webkit-transform: scale(1) translateY(0);}
                35%		{opacity: 1;}
                100% 	{-webkit-transform: scale(1.3) translateY(-15px); opacity: .3;}
            }
            @keyframes ag_bubbling {
                0% 		{transform: scale(1) translateY(0);}
                35%		{opacity: 1;}
                100% 	{transform: scale(1.3) translateY(-15px); opacity: .3;}
            }
			<?php
			break;	
			
			
		
		/* overlapping dots */
		case 'overlapping_dots':
			?>
            .ag_loader div:before,
            .ag_loader div:after {
                background-color: <?php echo $color ?>;
            }
            .agl_1 {
                width: 100%;
                height: 100%;
                border-radius: 50%;
                position: relative;
                vertical-align: middle;
                
                -webkit-animation: ag_overlap_dots1 1.73s infinite linear;
                animation: ag_overlap_dots1 1.73s infinite linear;
            }
            .agl_1:before,
            .agl_1:after {
                content:"";
                margin: -14px 0 0 -14px;
                width: 100%; 
                height: 100%;
                border-radius: 50%;
                position: absolute;
                top: 50%;
                left: 50%;
                
                -webkit-animation: ag_overlap_dots2 1.15s infinite ease-in-out;
                animation: ag_overlap_dots2 1.15s infinite ease-in-out;
            }
            .agl_1:after { 
                -webkit-animation-direction: reverse;
                animation-direction: reverse;
            }
            
            @-webkit-keyframes ag_overlap_dots1 {
                0% {	-webkit-transform: rotate(0deg); }
                100% { -webkit-transform: rotate(360deg); }
            }
            @keyframes ag_overlap_dots1 {
                0% {	 transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
            }
            
            @-webkit-keyframes ag_overlap_dots2 {
                0%	 { -webkit-transform: scale(0.2); left:	 0%; }
                50%	{ -webkit-transform: scale(1.0); left:	50%; }
                100% { -webkit-transform: scale(0.2); left: 100%; opacity: 0.5; }
            }
            @keyframes ag_overlap_dots2 {
                0%	 { transform: scale(0.2); left:	 0%; }
                50%	{ transform: scale(1.0); left:	50%; }
                100% { transform: scale(0.2); left: 100%; opacity: 0.5; }
            }
			<?php
			break;
			
			
			
		/* fading circles */
		case 'fading_circles':
			?>
            .ag_loader div:before,
            .ag_loader div:after {
                background-color: <?php echo $color ?>;
            }
            .agl_1 {
                width: 100%;
                height: 100%;
                border-radius: 50%;
                position: relative;
                vertical-align: middle;
            }
            .agl_1:before,
            .agl_1:after {
                content: "";
                width: 100%; 
                height: 100%;
                border-radius: 50%;
                position: absolute;
                top: 0;
                left: 0;
                
                -webkit-transform: scale(0);
                transform: scale(0);
            
                -webkit-animation: 	ag_fading_circles 1.4s infinite ease-in-out;
                animation: 			ag_fading_circles 1.4s infinite ease-in-out;
            }
            .agl_1:after { 
                -webkit-animation-delay: 0.7s;
                animation-delay: 0.7s;
            }
            @-webkit-keyframes ag_fading_circles {
                0%	 { -webkit-transform: translateX(-80%) scale(0); }
                50%	{ -webkit-transform: translateX(0)		scale(1); }
                100% { -webkit-transform: translateX(80%)	scale(0); }
            }
            @keyframes ag_fading_circles {
                0%	 { transform: translateX(-80%) scale(0); }
                50%	{ transform: translateX(0)		scale(1); }
                100% { transform: translateX(80%)	scale(0); }
            }
			<?php
			break;
		
	}
}
