(function () {
	
	function Rect() {
		this.x = 0;
		this.y = 0;
		this.width = -1;
		this.height = -1;
	};
	
	var m_vAllNodes = new Array();

	var	m_iFontLineHeight 	= 0,
		m_iFontLineDescent 	= 0,
		m_yLine				= 0,
		m_iInterBoxSpace 	= 10,
		m_iBoxBufferSpace 	= 2,
		m_iNodeRounding		= 4,
		m_iTallestBoxSize	= 0,
		m_iToolbarYPad		= 15,
		m_iMinBoxWidth		= 40,
		m_iToolbarXPos		= 0,
		m_iPortraitXPos		= 0,
		m_iPortraitYPos		= 0,
		m_iPortraitYPad = 45,// portrait height + padding bottom // @todo update if portrait height changes
		BOX_Y_DELTA			= 40,
		iMaxHoverPicHeight	= 150,
		iMaxHoverPicWidth	= 150,
		aFamilyTreeElement 	= null,
		sUnknownGenderLetter= null;
	

	var bOneNamePerLine 		= true,
		bOnlyFirstName 			= false,
		bBirthAndDeathDates 	= true,
		bConcealLivingDates 	= true,
//		bDeath 					= true,
		bShowSpouse 			= true,
		bShowOneSpouse			= false,
		bVerticalSpouses		= false,
		bMaidenName 			= true,
		bShowGender				= true,
		bDiagonalConnections	= false,
		bRefocusOnClick			= false,
		bShowToolbar			= true;

	var m_Canvas,
		m_CanvasRect;
	
	var iCanvasWidth = 100,
		iCanvasHeight = 100;
	
	this.familytreemain = function() {
		aFamilyTreeElement = document.getElementById("familytree");
		if (!aFamilyTreeElement) {
			return;
		}
		m_Canvas 	=  Raphael("familytree", iCanvasWidth, iCanvasHeight);
        m_Canvas.clear();
		m_CanvasRect = m_Canvas.rect(0, 0, iCanvasWidth, iCanvasHeight, 10).attr({fill: canvasbgcol, stroke: "none"}).toBack();

        text_sStartName = document.getElementById("focusperson");
        
        createTreeFromArray(tree_txt);
        loadImages();
        loadDivs();
        loadShortInfo();
        loadLongInfo();
        redrawTree();
	};

	this.redrawTree = function() {
		text_sStartName.value = text_sStartName.value.replace("\n", "");
		text_sStartName.value = text_sStartName.value.replace("\n", "");
		var sPerson = text_sStartName.value;
		var n = find(sPerson);		// Node n
		if (n == null) {
//			alert("Sorry, \'"+sPerson + "\' is not part of the tree");
			return;
		}
		iCanvasWidth = 100;
		iCanvasHeight = 100;
        m_Canvas.clear();
        freeNodesAllocatedTexts();
        resetObjectStates();
		m_CanvasRect = m_Canvas.rect(0, 0, iCanvasWidth, iCanvasHeight, 10).attr({fill: canvasbgcol, stroke: "none"}).toBack();
        printTreeFromNode(sPerson);
	};

	function Node(sID) {
		var m_sFTID			= sID,
			m_sName			= "?",
			m_sImageURL		= null,
			m_MyToolbarDiv	= null,
			m_MyThumbnailDiv	= null,
//			m_MyDivRaph		= null,
			m_sShortInfoURL	= null,
			m_sLongInfoURL	= null,
			m_sMaiden		= null,
			m_iBirthYear	= -1,
			m_sGender 		= sUnknownGenderLetter,
//			m_nSpouse	= null,
			m_iMyBranchWidth = 0;
		
		var m_vParents		= new Array(), 		// Guess this will be 0, 1 or 2 nodes only
			m_vChildren		= new Array(),
			m_vSpouses		= new Array();
		var	m_MyRect 		= new Rect(),		// bounding box for this node
			m_BothRect 		= new Rect();		// bounding box for this node + spouse node
		var m_RaphRect,							// Raphael's graphics box on canvas
			m_RaphTexts		= new Array();
		
		var	m_sBirthday		= null;
		var	m_sDeathday		= null;
		
		m_vAllNodes.push(this);		// Java.add()  -->  Javascript.push()		
		
		this.setSpouse = function(sID) {
			var nSpouse = findOrCreate(sID);
			connectSpouses(this, nSpouse);
		};
		
/*		this.setSpouseName = function(sName) {
			var nSpouse = findOrCreateName(sName);
			connectSpouses(this, nSpouse);
		};*/

		this.setMaiden = function(sMaidenName) {
			m_sMaiden = sMaidenName;
		};


/*		this.addChild = function(sChildName) {
			var nChild = findOrCreate(sChildName);
			connectParentChild(this, nChild);
			return nChild;
		}; */
		
		this.addParent = function(sParentID) {
			var nParent = findOrCreate(sParentID);
			connectParentChild(nParent, this);
			return nParent;
		};
		
/*		this.addParentName = function(sParentName) {
			var nParent = findOrCreateName(sParentName);
			connectParentChild(nParent, this);
			return nParent;
		};*/
		
		this.setBirthYear = function(iYear) {
			m_iBirthYear 	= iYear;
		};

		this.setBirthday = function(sDate) {	//"Birthday=19780213"
			m_sBirthday 	= sDate;
		};

		this.setDeathday = function(sDate) {	//"Deathday=19780213"
			m_sDeathday 	= sDate;
		};

		this.getBirthday = function() {	//"Birthday=19780213"
			return m_sBirthday;
		};

		this.getDeathday = function() {	//"Deathday=19780213"
			return m_sDeathday;
		};

		this.setGender = function(sGenderLetter) {
			var tmp = sGenderLetter.toLowerCase();
			if ((tmp != "f") && (tmp != "m")) {
				tmp = sUnknownGenderLetter;
			}
			m_sGender 	= tmp;
		};
				
		this.setImageURL = function(sURL) {
			m_sImageURL 	= sURL;
		};
		
		this.setToolbarDiv = function(sDivName) {
			m_MyToolbarDiv	= document.getElementById(sDivName);
//			m_MyDiv			= new Image();
		};

		this.setThumbnailDiv = function(sDivName) {
			m_MyThumbnailDiv	= document.getElementById(sDivName);
		};
		
		this.setShortInfoURL = function(sURL) {
			m_sShortInfoURL	= sURL;
		};
		
		this.setLongInfoURL = function(sURL) {
			m_sLongInfoURL 	= sURL;
		};
		
		this.getRaphRect = function() {
			return this.m_RaphRect;
		};
		
		this.getRaphTexts = function() {
			return this.m_RaphTexts;
		};
		
		this.setRaphTexts = function(arr) {
			this.m_RaphTexts = arr;
		};
		
		this.getFTID = function() {
			return m_sFTID;
		};
		
		this.setFTID = function(sID) {
			this.m_sFTID = sID;
		};
		
		this.getName = function() {
			return m_sName;
		};
		
		this.setName = function(sName) {
			m_sName = sName;
		};
		
		this.getImageURL = function() {
			return m_sImageURL;
		};
		
		this.getToolbarDiv = function() {
			return m_MyToolbarDiv;
		};

		this.getThumbnailDiv = function() {
			return m_MyThumbnailDiv;
		};

		this.getShortInfoURL = function() {
			return m_sShortInfoURL;
		};
		
		this.getLongInfoURL = function() {
			return m_sLongInfoURL;
		};
		
		this.getChildren = function() {
			return m_vChildren;
		};
		
		this.getParents = function() {
			return m_vParents;
		};

		this.getMyRect = function() {
			return m_MyRect;
		};

		this.getBothRect = function() {
			return m_BothRect;
		};

//		this.getSpouseName = function() {
//			return m_nSpouse == null ? "" : m_nSpouse.getName();
//		};
		
		this.getSpouses = function() {
			return m_vSpouses;
		}
		
		this.hasPartner = function(n) {
			var i = 0;
			var len = m_vSpouses.length;
			while (i < len) { 
				if (m_vSpouses[i++] == n)
					return true;
			}
			return false;
		};

		//private int countParentGenerations() {
		this.countParentGenerations = function() {
			var iCurrentDepth = 0;
			if (this.m_vParents != undefined) {
				var i = 0;
				var len = this.m_vParents.length;
				while (i < len) { 
					var p = this.m_vParents[i++]; 
					iCurrentDepth = Math.max(iCurrentDepth, p.countParentGenerations());
				}
			}
			return 1+iCurrentDepth;
		};
		

		//private int countChildrenGenerations() {
		this.countChildrenGenerations = function() {
			var iCurrentDepth = 0, iNumChildren = m_vChildren.length;
			var i = 0;
			var len = m_vChildren.length;
			while (i < len) { 
				iCurrentDepth = Math.max(iCurrentDepth, m_vChildren[i++].countChildrenGenerations());
			}
			if (iNumChildren != 0)
				return 1+iCurrentDepth;
			else
				return 0;
		};
		
		this.calcParentBranchWidths = function() {
			/*
			 * TODO
			 */
			return 0;
		};
		
		
		this.calcChildrenBranchWidths = function() {
			this.getMeAndSpousesGraphBoxes();
			var iMyWidth = m_iInterBoxSpace + m_BothRect.width;

			this.m_iMyBranchWidth = 0;
			var i = 0;
			var len = m_vChildren.length;
			while (i < len) { 
				this.m_iMyBranchWidth += m_vChildren[i++].calcChildrenBranchWidths();
			}
			
			if (iMyWidth > this.m_iMyBranchWidth)
				this.m_iMyBranchWidth = iMyWidth;
			
//			System.out.println("Width at "+getName()+" is "+this.m_iMyBranchWidth);
			
			return this.m_iMyBranchWidth;		
		};

		this.graphMe = function(iRelativePos, iGeneration) {
			var iY = this.getBoxY(iGeneration),
				iX = iRelativePos;
			iX += this.m_iMyBranchWidth/2;
			this.getGraphBox(iX, iY);
		};
		
		this.graphChildren = function(iRelativePos, iGeneration) {
			var iTotWidth = 0;
			var i = 0;
			var len = m_vChildren.length;
			while (i < len) { 
				var n = m_vChildren[i++];
				iTotWidth += n.m_iMyBranchWidth;
			}

			var iY = this.getBoxY(iGeneration),
				iX = iRelativePos-iTotWidth/2;
			
			i = 0;
			var len = m_vChildren.length;
			while (i < len) { 
				var n = m_vChildren[i];
				iX += n.m_iMyBranchWidth/2;
				m_vChildren[i].getGraphBox(iX, iY);
				m_vChildren[i].graphChildren(iX, 1+iGeneration);
				iX += n.m_iMyBranchWidth/2;
				i++;
			}
		};

		this.graphConnections = function() {
			var	iXFrom, iYFrom, iXTo, iYTo, iYMid;
			
			if (bShowSpouse && this.getSpouses().length != 0)
				iXFrom = m_MyRect.x+m_MyRect.width-1;
			else
				iXFrom = m_MyRect.x+m_MyRect.width/2;
			iYFrom = m_MyRect.y+m_MyRect.height-m_iNodeRounding;
			
			var i = 0;
			var len = m_vChildren.length;
			while (i < len) { 
				var n = m_vChildren[i++];
				iXTo = n.getMyRect().x + n.getMyRect().width/2;
				iYTo = n.getMyRect().y;
				iYMid = Math.round(0.5 + (iYFrom+iYTo)/2);
				
				if (bDiagonalConnections) 
					drawLine(iXFrom, iYFrom, iXTo, iYTo);

				else {
					drawLine(iXFrom, iYFrom, 	iXFrom, iYMid);
					drawLine(iXFrom, iYMid,		iXTo, 	iYMid);
					drawLine(iXTo,	 iYMid, 	iXTo, 	iYTo);
				}
				n.graphConnections();
			}

		};
		
		this.getBoxY = function(iRow) {
			BOX_LINE_Y_SIZE = parseInt(BOX_LINE_Y_SIZE);
			return (iRow * (m_iToolbarYPad + BOX_LINE_Y_SIZE)) + BOX_Y_DELTA;

			
	/*		if (iRow == 0)
				return BOX_Y_DELTA;
			
			Node nParent = m_vParents.get(0);
			if (iRow == 1)
				return nParent.m_MyRect.y + nParent.m_MyRect.height + BOX_Y_DELTA;
			
			Node nGrandParent = nParent.m_vParents.get(0);
			
			int iLargestY = 0;
			int iNumChildren = nGrandParent.m_vChildren.size();
			for (int i = 0; i < iNumChildren; ++i) {
				Node n = nGrandParent.m_vChildren.get(i);
				if ((n.m_MyRect.y+n.m_MyRect.height) > iLargestY)
					iLargestY = n.m_MyRect.y+n.m_MyRect.height;
			}
			return iLargestY+BOX_Y_DELTA;
	*/
		};
		
		/*
		 * Calculates the size of this node, the spouses nodes, and total, without printing them
		 */
		this.getMeAndSpousesGraphBoxes = function() {
			
			this.getGraphBox(0, 0); 		// set m_MyRect	
//			System.out.println("Got my rectwidth ("+getName()+") = "+m_MyRect.width);
			m_BothRect.x 		= m_MyRect.x;
			m_BothRect.y 		= m_MyRect.y;
			m_BothRect.width 	= m_MyRect.width;
			m_BothRect.height 	= m_MyRect.height;
			
			var mySpouses = this.getSpouses();
			if (bShowSpouse && (mySpouses.length != 0)) {
				var iTotalSpouseHeight = 0;
				var iTotalSpouseWidth = 0;
				var iSpHeight = 0;
				var aSpouse;
				
				var i = 0;
				var len = mySpouses.length;
				while (i < len) { 
//					aSpouse = m_vSpouses[i++];
					aSpouse = mySpouses[i++];
					aSpouse.getGraphBox(0, 0);	// set m_vSpouses[sp].m_MyRect
					iSpHeight = aSpouse.getMyRect().height;
					iSpWidth  = aSpouse.getMyRect().width;
					if (bVerticalSpouses) {
						iTotalSpouseHeight += iSpHeight;
						iTotalSpouseWidth = Math.max(iTotalSpouseWidth, iSpWidth);
					} else {
						iTotalSpouseHeight = Math.max(iTotalSpouseHeight, iSpHeight);
						iTotalSpouseWidth  += iSpWidth; 
					}
					if (bShowOneSpouse)
						break;			// We will only use first spouse in list
				}
				iTotalSpouseHeight = Math.max(m_MyRect.height, iTotalSpouseHeight);
				m_BothRect.width += iTotalSpouseWidth;
				m_BothRect.height = iTotalSpouseHeight;
				m_MyRect.height = iTotalSpouseHeight;
			
				var i = 0;
				var len = mySpouses.length;
				while (i < len) { 
					aSpouse = mySpouses[i++];
					if (bVerticalSpouses) {
						aSpouse.getMyRect().width = iTotalSpouseWidth;
					} else {
						aSpouse.getMyRect().height = iTotalSpouseHeight;
					}
					if (bShowOneSpouse)
						break;
				}
			}
			
			m_iTallestBoxSize = Math.max(m_iTallestBoxSize, m_MyRect.height);

/*			if (bShowSpouse && (this.m_nSpouse != null)) {
				this.m_nSpouse.getGraphBox(0, 0);	// set m_nSpouse.m_MyRect
//				System.out.println("Got spouses rectwidth ("+m_nSpouse.getName()+") = "+m_nSpouse.m_MyRect.width);
				var iLargestHeight = Math.max(m_MyRect.height, this.m_nSpouse.getMyRect().height);
				m_BothRect.width += this.m_nSpouse.getMyRect().width;
				m_BothRect.height = iLargestHeight;
				m_MyRect.height = iLargestHeight;
				this.m_nSpouse.getMyRect().height = iLargestHeight;
			}*/
			
//			if (m_MyRect.height > m_iLargestBoxHeight)
//				m_iLargestBoxHeight = m_MyRect.height;
		};
				
		/*
		 * Calculate the size of this node and print it
	 	 * Formatting flags: bOneNamePerLine bOnlyFirstName bBirthAndDeathDates bDeath bShowSpouse bMaidenName bShowGender bDiagonalLines		
		 */
		this.getGraphBox = function(X, Y) {
			var bPrint = (X != 0) || (Y != 0);
			var r = new Rect();
			resetLine();	// Which line our "cursor" is on while printing in box.

			if (!bPrint) {
				m_MyRect.width = m_iMinBoxWidth;

				// add room for toolbar at bottom of node box
				m_MyRect.height = m_iPortraitYPad + m_iToolbarYPad;

				m_BothRect.width = 0;
				m_BothRect.height = 0;
			}
			r.x = m_MyRect.x;
			r.y = m_MyRect.y;
			r.width = m_MyRect.width;
			r.height = m_MyRect.height;

			if (bPrint) {

				this.m_RaphRect = m_Canvas.rect();
				r.x = X-m_BothRect.width/2;
				r.y = Y;






				growCanvas(r.x+r.width, r.y+r.height+1);
				this.m_RaphRect.attr({	
										"x": r.x,
										"y": r.y,
										"width": r.width,
										"height": r.height+1,
										r: m_iNodeRounding
								});
				this.m_RaphRect.attr({stroke: nodeoutlinecol, fill: nodefillcol, "fill-opacity": nodefillopacity});
//				m_Canvas.rect(r.x, r.y, r.width, r.height+1, 4)
//							.attr({stroke: "#ff0", fill: "#0ff", "fill-opacity": .4
				this.m_RaphRect.show();
				this.m_RaphRect.click(function () {
					if (bRefocusOnClick) {
						var n = findRectOwningNode(this);
						if (n != null) {
							text_sStartName.value = n.getName();
							redrawTree();
						}
					}
                }).mouseover(function (ev) {// @todo maybe remove this and mouseout
                    this.animate({"fill-opacity": .75}, 300);
                }).mouseout(function () {
                    this.animate({"fill-opacity": nodefillopacity}, 300);
                });

				
				// add portrait

				var n = findRectOwningNode(this.m_RaphRect);

				if (n != null) {

					var imgEl = document.getElementById('ftportrait' + n.getFTID());

					if (imgEl != null) {

						imgEl.src = encodeURI(n.getImageURL());

					}
				}                    			
	






			}


			
			var sGender = (bShowGender 	&& (m_sGender != null)) ? " ("+m_sGender+")" : "";
			var sMaiden = (bMaidenName 	&& (m_sMaiden != null)) ? " ("+m_sMaiden+")" : "";
			

						

			if (bOnlyFirstName) {
				// split full name into list of single names
				sTokens = this.getName().split(" ");
				r = makeGraphBox(bPrint, r, sTokens[0] + sGender, this);

			} else {
				if (bOneNamePerLine) {

					
					sTokens = this.getName().split(" ");
					for (var i = 0; i < sTokens.length; ++i) {
						if (i == sTokens.length-1)
							r = makeGraphBox(bPrint, r, sTokens[i] + sGender, this);
						else
							r = makeGraphBox(bPrint, r, sTokens[i], this);
							
					

					}




					if (bMaidenName && (m_sMaiden != null))
						r = makeGraphBox(bPrint, r, sMaiden, this);
					
				} else {
					r = makeGraphBox(bPrint, r, this.getName() + sGender + sMaiden, this);	
				}
				
			}
			
			if (bBirthAndDeathDates) {

				var birth = this.getBirthday() != null ? this.getBirthday() : "", 
					death = this.getDeathday() != null ? this.getDeathday() : ""; 
				if (death != "" || (birth != "" && !bConcealLivingDates))
					r = makeGraphBox(bPrint, r, "("+birth+" - "+death + ")", this);	
			}
			

			// final box size adjustments
			r.height += 3;
			r.width += 2;
			
			m_MyRect.x 		= r.x;	// Save the size of this node's box
			m_MyRect.y 		= r.y;
			m_MyRect.width 	= r.width;
			m_MyRect.height = r.height;


			if (bPrint && bShowSpouse) {
				
				var mySpouses = this.getSpouses();
				var aSpouse;
				var iH, iW;
				var xpos = r.x+r.width; /*+m_nSpouse.m_MyRect.width/2 */
				var ypos = r.y;
				var i = 0;
				var len = mySpouses.length;
				while (i < len) { 
					aSpouse = mySpouses[i++];
					iH = aSpouse.getMyRect().height;
					iW = aSpouse.getMyRect().width;
					// Print this node's spouses
					bShowSpouse = false;	// so we don't get an infinite loop
					aSpouse.getGraphBox(xpos, ypos);
					bShowSpouse = true;
				
					if (bShowOneSpouse)		// Only showing ONE spouse
						break;

					if (bVerticalSpouses) {
						ypos += iH;
					} else {
						xpos += iW;
					}					
				}
			}

//			if (bPrint && bShowSpouse && (this.m_nSpouse != null)) {
//				// Print this node's spouse
//				bShowSpouse = false;	// so we don't get an infinite loop
//				this.m_nSpouse.getGraphBox(r.x+r.width /*+m_nSpouse.m_MyRect.width/2 */, r.y);
//				bShowSpouse = true;
//			}
		};
		
		this.setImage = function(img) {

		};
		
		this.setDiv = function(div) {
			m_MyDiv = div;
//			m_MyDivRaph = Raphael(div);
		};
		
	}	// End of 'Node' class declaration
	

	
	// File global name space

	function getPageEventCoords(evt) {
		var coords = {left:0, top:0};
		if (evt.pageX) {
			coords.left = evt.pageX;
			coords.top = evt.pageY;
		} else if (evt.clientX) {
			coords.left = evt.clientX + document.body.scrollLeft - document.body.clientLeft;
			coords.top  = evt.clientY + document.body.scrollTop - document.body.clientTop;
			// include html element space, if applicable
			if (document.body.parentElement && document.body.parentElement.clientLeft) {
				var bodParent = document.body.parentElement;
				coords.left += bodParent.scrollLeft - bodParent.clientLeft;
				coords.top  += bodParent.scrollTop  - bodParent.clientTop;
			}
		}
		return coords;
	}

	
	
	
	
	function findRectOwningNode(rect) {
		var i = 0;
		var len = m_vAllNodes.length;
		while (i < len) { 
			var anode = m_vAllNodes[i++],
				bnode = anode.getRaphRect(); 
			if (bnode != null) {
				if (bnode == rect)
					return anode;
			}
		}
		return null;
	}
	
	function findTextOwningNode(textobj) {
		var i = 0;
		var len = m_vAllNodes.length;
		while (i < len) { 
			var anode = m_vAllNodes[i++];
			var	texts = anode.getRaphTexts();

			var j = 0;
			var len2 = texts.length;
			while (j < len2) { 
				if (texts[j++] == textobj) 
					return anode;
			}
		}
		return null;
	}
	
	function growCanvas(w, h) {
		iCanvasWidth = Math.max(w+2, iCanvasWidth+2);
		iCanvasHeight= Math.max(h+2, iCanvasHeight+2);
		m_Canvas.setSize(iCanvasWidth, iCanvasHeight);
		m_CanvasRect.attr({
			x: 0, 
			y: 0, 
			width: iCanvasWidth, 
			height: iCanvasHeight, 
			r: 10}).attr({fill: canvasbgcol, stroke: "none"}).toBack();
	}
	
	function drawLine(iXFrom, iYFrom, 	iXTo, iYTo) {
		m_Canvas.path(
				"M"+iXFrom+" "+iYFrom+
				"L"+iXTo+" "+iYTo);
							
	}


	function find(sFTID) {
		var n;
		for (var i = 0; i < m_vAllNodes.length; ++i) {
			n = m_vAllNodes[i];
			if (n.getFTID().toLowerCase() == sFTID.toLowerCase())
				return n;
		}
		return null;
	}

	function findName(sName) {
		var n;
		for (var i = 0; i < m_vAllNodes.length; ++i) {
			n = m_vAllNodes[i];
			if (n.getName() != null)
				if (n.getName().toLowerCase() == sName.toLowerCase())
					return n;
		}
		return null;
	}

	// Find, or else create, a Node based on a Family Tree ID
	function findOrCreate(sFTID) {
		if (sFTID == null)
			return null;
		
		var nFound = find(sFTID);
		
		if (nFound == null) {
			nFound = new Node(sFTID);
		}
		
		return nFound;
	}
	
	// Find, or else create, a Node based on a Family Tree ID
	function findOrCreateName(sName) {
		var nFound = findName(sName);
		
		return findOrCreate(findName(sName));
	}

	function connectParentChild(p, c) {	// connect Node p (parent) and Node c (child)
		var bFound = false;
		var ch = p.getChildren();

		var i = 0;
		var len = ch.length;
		while (i < len) { 
			if (ch[i++] == c) {
				bFound = true;
				break;
			}
		}
		if (bFound == false)
			ch.push(c);
		
		
//		if (!p.m_vChildren.contains(c))
//			p.m_vChildren.push(c);	
//		if (!c.m_vParents.contains(p))
//			c.m_vParents.push(p);
		
		bFound = false;
		var pa = c.getParents();
		var i = 0;
		var len = pa.length;
		while (i < len) { 
			if (pa[i++] == p) {
				bFound = true;
			}
		}
		if (bFound == false)
			pa.push(p);
	}
	
	function connectSpouses(s1, s2) {	// connect Nodes s1 and s2
		if (s1.hasPartner(s2))
			; //alert("Error in tree: "+s1.getName()+" already connected to "+s2.getName());
		else
			s1.getSpouses().push(s2);
		
		if (s2.hasPartner(s1))
			; //alert("Error in tree: "+s2.getName()+" already connected to "+s1.getName());
		else
			s2.getSpouses().push(s1);
	}
	
	function printTreeFromNode(sID) {
		var n = find(sID);		// Node n
	
		// get metrics from the graphics
//X		m_FontMetrics = g.getFontMetrics(g.getFont());
		// get the height of a line of text in this font and render context
		m_iFontLineHeight = 10;	//X m_FontMetrics.getHeight();
		m_iFontLineDescent = 4;	//X m_FontMetrics.getDescent();
		m_iTallestBoxSize = 0;
		
		if (n == null) {
			alert("Sorry, \'"+sID + "\' is not part of the tree");
			return;
		}
		
		// Where to draw a node depends on the total size of its branches
		n.countParentGenerations();
		n.countChildrenGenerations();
		n.calcParentBranchWidths();		// TODO
		n.calcChildrenBranchWidths();
		
//		m_iLargestBoxHeight = 0;
//X		m_CurrentGraphics.setColor(Color.white);	// drawing colour
		
		// Draw graph boxes
		n.graphMe(0, 0);
		n.graphChildren(n.m_iMyBranchWidth/2, 1);
		
		// Draw interconnect lines
		n.graphConnections();
	}
	
	function getPixelsPerLine() {
		return 14;//m_iFontLineHeight+m_iFontLineDescent;
	}

	function resetLine() {
		m_yLine = 0;
	}
	
	function getLine() {
		return m_yLine;
	}
	
	function incLine() {
		++m_yLine;
	}
	
	/*
	 * makeGraphBox()
	 * 
	 * This method draws the box virtually or really. (bPrintIt)
	 * 
	 * The first time around, we call it with bPrintIt = false, so we can find out
	 * the resulting size of the box. Next time around, we know where the box will be positioned
	 * (determined by its size) so we draw it for real.
	 * 
	 * Short: If bPrintIt is true, the box rectangle (theBox) isn't touched. It stays intact.
	 */
	function makeGraphBox(bPrintIt, theBox, sAddString, node) {
		// get the advance of my text in this font and render context
				
		if (bPrintIt) {
			var w = 0;

			var theRaphText = m_Canvas.text(0, 0, sAddString != null ? decodeURI(sAddString) : "");

			theRaphText.attr({"fill": nodetextcolour});
			node.getRaphTexts().push(theRaphText);
			
			w = theRaphText.getBBox().width;

			w = 0;	//X Remove this line if Java!
			theRaphText.attr({
				x: theBox.x + (theBox.width - w)/2 + m_iBoxBufferSpace - 2, 
				y: m_iPortraitYPad + theBox.y + m_iFontLineHeight + getLine()*getPixelsPerLine()
			}).toFront();
			
			var toolbardiv = node.getToolbarDiv();
			if (bShowToolbar && (toolbardiv != null)) {
				// NOTE! For style.width to work on Firefox, the div should include style.width = numberpx!
	

				var tbw = parseInt(toolbardiv.offsetWidth);
				
				toolbardiv.style.visibility="visible";

				toolbardiv.style.left = m_iToolbarXPos + theBox.x + (theBox.width - tbw)/2+'px';

				toolbardiv.style.top  = (theBox.y + theBox.height - m_iToolbarYPad) +'px';

	
			}

			var thumbnaildiv = node.getThumbnailDiv();

			if (thumbnaildiv != null) {

				// @todo update 40 to portrait width if needed
				thumbnaildiv.style.left = m_iPortraitXPos + theBox.x + (theBox.width - 40)/2 + 'px';//center the image
				thumbnaildiv.style.top  = m_iPortraitYPos + theBox.y + 'px';				
				
			}

			theRaphText.click(function () {
				if (bRefocusOnClick) {
					var n = findTextOwningNode(this);
					if (n != null) {
						text_sStartName.value = n.getName();
						redrawTree();
					}
				}
            });
			
			incLine();

		} else {
			var w = 0;
//FONT			var h = 0;
			if (sAddString != null) {
				var temptxt = m_Canvas.text(0, 0, decodeURI(sAddString));
//FONT				temptxt.attr({"font": '18px "Helvetica Neue", Helvetica, "Arial Unicode MS", Arial, sans-serif '});
//				temptxt.attr({"font": '24px "Helvetica Neue", Helvetica, "Arial Unicode MS", Arial, sans-serif '});
//				temptxt.attr({"color": '#0f0'});
				w = temptxt.hide().getBBox().width;
//FONT				h = temptxt.hide().getBBox().height;
				temptxt.remove();
			}
			w += 2*m_iBoxBufferSpace+1;
//			if (w < m_iMinBoxWidth)
//				w = m_iMinBoxWidth;
			if (w > theBox.width)
				theBox.width = w;
	
			theBox.height += getPixelsPerLine(); //h; //FONT



		}
		
		return theBox;
	}
	
	function isValidDate(sDate) {
		var dlen = sDate.length;
		if ((dlen != 4) && (dlen != 6) && (dlen != 8))
			return false;
	}
	
	function createTreeFromArray(sArray) {
       
		var n = null,
		sKey = null;
		var i = 0;
		var len = sArray.length;
		while (i < len) {
        	var sLine = sArray[i++];
        	var sTokens = sLine.split("=");

			if ((sTokens.length < 1) || (sTokens[0].charAt(0) == '#'))
				continue;
			
			sKey = sTokens[0].toLowerCase();

			if (sKey == "esscottiftid") {
				n = findOrCreate(sTokens[1]);	// Guarantees the node exists after receiving "esscottiftid" key
				
			} else if (sKey == "name") {
				n.setName(sTokens[1]);

			} else if (sKey == "imageurl") {
				n.setImageURL(sTokens[1]);
				
			} else if (sKey == "toolbar") {
				n.setToolbarDiv(sTokens[1]);
				
			} else if (sKey == "thumbnaildiv") {
				n.setThumbnailDiv(sTokens[1]);
				
			} else if (sKey == "shortinfourl") {
				n.setShortInfoURL(sTokens[1]);
				
			} else if (sKey == "longinfourl") {
				n.setLongInfoURL(sTokens[1]);
				
			} else if (sKey == "male") {
				n.setGender("m");
				
			} else if (sKey == "female") {
				n.setGender("f");
				
			} else if (sKey == "spouse") {	// ID!
				n.setSpouse(sTokens[1]);			// Guarantees the node exists after receiving "spouse" key
				
//bad			} else if (sKey == "spousename") {	// by name - less secure
//bad				n.setSpouseName(sTokens[1]);
				
			} else if (sKey == "maiden") {
				n.setMaiden(sTokens[1]);
				
			} else if (sKey == "year") {	// obsolete
				n.setBirthYear(sTokens[1]);
				
			} else if (sKey == "birthday") {
				n.setBirthday(sTokens[1]);
				
			} else if (sKey == "deathday") {
				n.setDeathday(sTokens[1]);
				
			} else if (sKey == "parent") {	// ID!
				n.addParent(sTokens[1]);			// Guarantees the node exists after receiving "parent" key
				
//bad			} else if (sKey == "parentname") {	// by name - less secure
//bad				n.addParentName(sTokens[1]);
				
			} else if (sKey == "child") {	// obsolete
				n.addChild(sTokens[1]);


			} else
				alert("Error in family tree file: " + sTokens[0]+" "+sTokens[1]);

		}
	}	
	
	function freeNodesAllocatedTexts() {
		var i = 0;
		var len = m_vAllNodes.length;
		while (i < len) { 
			m_vAllNodes[i].setRaphTexts(null);
			m_vAllNodes[i].setRaphTexts(new Array());
			i++;
		}
	}
	
	function resetObjectStates() {
		var i = 0;
		var len = m_vAllNodes.length;
		while (i < len) { 
			var n = m_vAllNodes[i++];
			var aToolbarDiv = n.getToolbarDiv();
			if (aToolbarDiv == null)
				continue;
			aToolbarDiv.style.visibility="hidden";
		}
	}
	
/*	Not in use, but keep here. May come in handy some time

  	function createDiv(name, content) {
		var divTag = document.createElement("div");
		
		divTag.id = name;
		divTag.style.position = "absolute";
//		divTag.setAttribute("align","center");
		divTag.style.left =  '0px';
		divTag.style.top = '0px';
		divTag.style.width = '30px';
		divTag.style.height = '30px';
		divTag.style.margin = "0px auto";
		divTag.style.visibility = "hidden";
		divTag.className ="dynamicDiv";
		document.body.appendChild(divTag);
		divTag.innerHTML = content;
		return divTag;
	} */

    function loadImages() {
        var i = 0;
        var len = m_vAllNodes.length;
        while (i < len) { 
			var n = m_vAllNodes[i++];
			var sUrl = n.getImageURL();
			if (sUrl == null)
				continue;
			var img = new Image();
			n.setImage(img);
			img.src = encodeURI(sUrl);
			img.onload = function() {
			    var max_height = iMaxHoverPicHeight;
			    var max_width = iMaxHoverPicWidth;

			    var height = this.height;
			    var width = this.width;
			    var ratio = height/width;

			    // If height or width are too large, they need to be scaled down
			    // Multiply height and width by the same value to keep ratio constant
			    if (height > max_height)
			    {
			        ratio = max_height / height;
			        height = height * ratio;
			        width = width * ratio;
			    }

			    if (width > max_width)
			    {
			        ratio = max_width / width;
			        height = height * ratio;
			        width = width * ratio;
			    }

			    this.width = width;
			    this.height = height;
			};
		}
	}
	
	function loadDivs() {
        var i = 0;
        var len = m_vAllNodes.length;
        while (i < len) { 
			var n = m_vAllNodes[i++];
			var aToolbarDiv = n.getToolbarDiv();
			if (aToolbarDiv == null)
				continue;
//			var div = Raphael("someElement", "20%", "20%");//new Image();
//>>>			var div = createDiv(n.getFTID(), sDivContents);
//			n.setDiv(Raphael(div, 50, 50));

//			aFamilyTreeElement.appendChild(img);
			aToolbarDiv.style.visibility="hidden";
//			div.src = encodeURI(sUrl);
/*			div.onload = function() {
			    var max_height = iMaxHoverPicHeight;
			    var max_width = iMaxHoverPicWidth;

			    var height = this.height;
			    var width = this.width;
			    var ratio = height/width;

			    // If height or width are too large, they need to be scaled down
			    // Multiply height and width by the same value to keep ratio constant
			    if (height > max_height)
			    {
			        ratio = max_height / height;
			        height = height * ratio;
			        width = width * ratio;
			    }

			    if (width > max_width)
			    {
			        ratio = max_width / width;
			        height = height * ratio;
			        width = width * ratio;
			    }

			    this.width = width;
			    this.height = height;
			}; */
		}
	}

	function loadShortInfo() {
        var i = 0;
        var len = m_vAllNodes.length;
        while (i < len) { 
			var n = m_vAllNodes[i++];
			var sUrl = n.getShortInfoURL();
			if (sUrl == null)
				continue;
			
			
		}
	};
	
	function loadLongInfo() {
        var i = 0;
        var len = m_vAllNodes.length;
        while (i < len) { 
			var n = m_vAllNodes[i++];
			var sUrl = n.getLongInfoURL();
			if (sUrl == null)
				continue;
			
			
		}
	};

	this.setOneNamePerLine = function(bState) 		{ bOneNamePerLine = bState; 			//redrawTree(); 
	};
	this.setOnlyFirstName = function(bState) 		{ bOnlyFirstName = bState; 				//redrawTree(); 
	};
	this.setBirthAndDeathDates = function(bState) 	{ bBirthAndDeathDates = bState; 		//redrawTree(); 
	};
	this.setConcealLivingDates = function(bState)	{ bConcealLivingDates = bState; 		//redrawTree(); 
	};
	this.setDeath = function(bState) 				{ bDeath = bState; 							//redrawTree(); 
	};
	this.setShowSpouse = function(bState) 			{ bShowSpouse = bState; 					//redrawTree(); 
	};
	this.setShowOneSpouse = function(bState) 		{ bShowOneSpouse = bState; 				//redrawTree(); 
	};
	this.setVerticalSpouses = function(bState) 		{ bVerticalSpouses = bState; 			//redrawTree(); 
	};
	this.setMaidenName = function(bState) 			{ bMaidenName = bState; 					//redrawTree(); 
	};
	this.setShowGender = function(bState) 			{ bShowGender = bState; 					//redrawTree(); 
	};
	this.setDiagonalConnections = function(bState)	{ bDiagonalConnections = bState; 	//redrawTree(); 
	};
	this.setRefocusOnClick = function(bState)		{ bRefocusOnClick = bState; 				//redrawTree(); 
	};
	this.setShowToolbar = function(bState)			{ bShowToolbar = bState; 					//redrawTree(); 
	};
	this.setNodeRounding = function(iRadius)		{ m_iNodeRounding = iRadius;	};
	this.setToolbarYPad = function(iYPad) {
		m_iToolbarYPad = iYPad;
	};
	this.setMinBoxWidth = function(iMinWidth)		{ m_iMinBoxWidth = iMinWidth;	};

	this.setPortraitPos = function(iX, iY)	{ m_iPortraitXPos = iX; m_iPortraitYPos = iY;
	};	

	this.getShowOneSpouse = function() 				{ return bShowOneSpouse;		};
	this.getShowGender = function() 				{ return bShowGender; 			};
	this.getRefocusOnClick = function() 			{ return bRefocusOnClick; 		};
	this.getShowToolbar = function() 				{ return bShowToolbar; 			};
	this.getNodeRounding = function() 				{ return m_iNodeRounding;		};
	this.getNodeRounding = function()				{ return m_iNodeRounding = iRadius;	};
	this.onFocusPersonChanged = function(e) {
//		value = value.replace("\n", "");

		//var strippedString:String=oldString.split("\n").join(" ");
		
/* TODO Get this to work!
 * 
 * 		if (!e) var e = window.event;
		if (e.keyCode) code = e.keyCode;
		else if (e.which) code = e.which;

		if (code == 13)
			redrawTree();*/
	};
	
	
})();
