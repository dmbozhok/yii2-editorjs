class Ejs {

	constructor(id, uploadFile, uploadUrl, uploadLink, form, name, content) {
		this.uploadFile = uploadFile;
		this.uploadLink = uploadLink;
		this.uploadUrl = uploadUrl;
		this.addRequestData = {};
		this.editors = [];
		this.editor = undefined;

		let meta1 = document.querySelectorAll("meta[name=\"csrf-param\"]");
		let meta2 = document.querySelectorAll("meta[name=\"csrf-token\"]");
		if (meta1.length > 0 && meta2.length > 0) {
			this.addRequestData[meta1[0].content] = meta2[0].content;
		}
		this.start(id, form, name, content);
	}

	get tools() {
		let ejs_tools = {
			header: {
				class: Header,
				shortcut: "CMD+SHIFT+H",
				config: {
					placeholder: "Enter a header"
				}
			},
			list: {
				class: List,
				inlineToolbar: true,
			},
			paragraph: {
				class: Paragraph,
				inlineToolbar: true,
			},
			warning: {
				class: Warning,
				inlineToolbar: true,
				shortcut: "CMD+SHIFT+W",
				config: {
					titlePlaceholder: "Title",
					messagePlaceholder: "Message",
				},
			},
			code: {
				class: CodeTool,
				config: {
					placeholder: "Enter code"
				}
			},
			delimiter: {
				class: Delimiter
			},
			table: {
				class: Table,
				inlineToolbar: true,
				config: {
					rows: 2,
					cols: 3,
				}
			},
			inlineCode: {
				class: InlineCode,
				shortcut: 'CMD+SHIFT+I',
			},
			quote: {
				class: Quote,
				inlineToolbar: true,
				shortcut: 'CMD+SHIFT+Q',
				config: {
					quotePlaceholder: 'Enter a quote',
					captionPlaceholder: 'Quote\'s author',
				},
			},
			marker: {
				class: Marker,
				shortcut: 'CMD+SHIFT+M',
			},
			raw: {
				class: RawTool,
				config: {
					placeholder: 'Enter html code',
				}
			},
			embed: {
				class: Embed,
				//inlineToolbar: true,
				config: {
					services: {
						youtube: true,
						coub: true,
						codepen: {
							regex: /https?:\/\/codepen.io\/([^\/\?\&]*)\/pen\/([^\/\?\&]*)/,
							embedUrl: 'https://codepen.io/<%= remote_id %>?height=300&theme-id=0&default-tab=css,result&embed-version=2',
							html: "<iframe height='300' scrolling='no' frameborder='no' allowtransparency='true' allowfullscreen='true' style='width: 100%;'></iframe>",
							height: 300,
							width: 600,
							id: (groups) => groups.join('/embed/')
						}
					}
				}
			}
		};
		if (this.uploadFile || this.uploadUrl) {
			ejs_tools["image"] = {
				class: ImageTool,
				config: {
					endpoints: {},
					additionalRequestData: this.addRequestData,
					field: "image",
					types: "*/*",
					captionPlaceholder: "Enter caption",
				}
			};
		}
		if (this.uploadFile) {
			ejs_tools["image"].config.endpoints["byFile"] = this.uploadFile;
		}
		if (this.uploadUrl) {
			ejs_tools["image"].config.endpoints["byUrl"] = this.uploadUrl;
		}
		if (this.uploadLink) {
			ejs_tools["linkTool"] = {
				class: LinkTool,
				config: {
					endpoint: this.uploadLink,
					additionalRequestData: this.addRequestData,
				}
			};
		}
		return ejs_tools;
	} // end tools

	start(id, form, name, content) {
		var _self = this;
		let e = document.getElementById(id);
		if (e) {
			if (!e.dataset.editorjsNum) {
				if (form) {
					var formElem = document.getElementById(form);
				}
				if (name) {
					var formFields = document.getElementsByName(name);
				}
				this.editor = new EditorJS({
					holderId: id,
					tools: this.tools,
					onChange: () => {
						if (formFields) {
							this.editor.save().then(
								(output) => {
									let res_val = JSON.stringify(output);
									for (let i = 0; i < formFields.length; i++) {
									/*if (formElem) {
										if (formElem == formFields[i].form) {
											formFields[i].value = res_val;
										}
									} else*/ {
											formFields[i].value = res_val;
										}
									}
								}
							);
						}
					},
					onReady: () => {
						if (content) {
							this.loadJson(content);
						}
					}
				});
				e.dataset.editorjsNum = 1;
				/*if (form) {					
					$(document).on("pjax:success", function (e, data, status, xhr, options) {
						console.log(e, data, status, xhr, options);
						if (e.relatedTarget == form) {
							_self.start(id, form, name);
						}
					});
				}*/
				//return idx;
			}
		}
	} // end start

	loadBlocks(blocks) {
		if (this.editor) {
			this.editor.render(blocks);
		}
	} // end loadBlocks

	loadJson(jsonContent) {
		if (jsonContent) {
			try {
				let data = JSON.parse(jsonContent);
				this.loadBlocks(data);
			} catch (e) {
				console.log("Error load json content", e);
			}
		}
	} // end loadJson
} // end class

