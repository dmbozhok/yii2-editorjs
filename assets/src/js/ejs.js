const EditorJS = require('@editorjs/editorjs');
const List = require('@editorjs/list');
const Paragraph = require('@editorjs/paragraph');

class Ejs {
	constructor(id, uploadFile, uploadUrl, uploadLink, form, name, content, placeholder = '') {
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
		this.start(id, form, name, content, placeholder);
	}

	get tools() {
		let ejs_tools = {
			list: {
				class: List,
				inlineToolbar: true,
			},
			paragraph: {
				class: Paragraph,
				inlineToolbar: true,
			}
		};
		return ejs_tools;
	} // end tools

	start(id, form, name, content, placeholder = '') {
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
					holder: id,
					tools: this.tools,
					placeholder: placeholder,
					inlineToolbar: ['bold', 'italic'],
					minHeight : 60,
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
					},
					/**
					 * Internationalzation config
					 */
					i18n: {
						/**
						 * @type {I18nDictionary}
						 */
						messages: {
							/**
							 * Other below: translation of different UI components of the editor.js core
							 */
							ui: {
								"blockTunes": {
									"toggler": {
										"Click to tune": "Нажмите, чтобы настроить",
											"or drag to move": "или перетащите"
									},
								},
								"inlineToolbar": {
									"converter": {
										"Convert to": "Конвертировать в"
									}
								},
								"toolbar": {
									"toolbox": {
										"Add": "Добавить"
									}
								}
							},

							/**
							 * Section for translation Tool Names: both block and inline tools
							 */
							toolNames: {
								"Text": "Параграф",
									"Heading": "Заголовок",
									"List": "Список",
									"Warning": "Примечание",
									"Checklist": "Чеклист",
									"Quote": "Цитата",
									"Code": "Код",
									"Delimiter": "Разделитель",
									"Raw HTML": "HTML-фрагмент",
									"Table": "Таблица",
									"Link": "Ссылка",
									"Marker": "Маркер",
									"Bold": "Полужирный",
									"Italic": "Курсив",
									"InlineCode": "Моноширинный",
							},

							/**
							 * Section for passing translations to the external tools classes
							 */
							tools: {
								/**
								 * Each subsection is the i18n dictionary that will be passed to the corresponded plugin
								 * The name of a plugin should be equal the name you specify in the 'tool' section for that plugin
								 */
								"warning": { // <-- 'Warning' tool will accept this dictionary section
									"Title": "Название",
										"Message": "Сообщение",
								},

								/**
								 * Link is the internal Inline Tool
								 */
								"link": {
									"Add a link": "Вставьте ссылку"
								},
								/**
								 * The "stub" is an internal block tool, used to fit blocks that does not have the corresponded plugin
								 */
								"stub": {
									'The block can not be displayed correctly.': 'Блок не может быть отображен'
								}
							},

							/**
							 * Section allows to translate Block Tunes
							 */
							blockTunes: {
								/**
								 * Each subsection is the i18n dictionary that will be passed to the corresponded Block Tune plugin
								 * The name of a plugin should be equal the name you specify in the 'tunes' section for that plugin
								 *
								 * Also, there are few internal block tunes: "delete", "moveUp" and "moveDown"
								 */
								"delete": {
									"Delete": "Удалить"
								},
								"moveUp": {
									"Move up": "Переместить вверх"
								},
								"moveDown": {
									"Move down": "Переместить вниз"
								}
							},
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
}; // end class
module.exports = Ejs;
