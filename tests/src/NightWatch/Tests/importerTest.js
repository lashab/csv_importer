module.exports = {
  '@tags': ['your_module'],
  before: function(browser) {
    browser
      .drupalInstall();
  },
  after: function(browser) {
    browser
      .drupalUninstall();
  },
  'Visit a test page and create some test page': (browser) => {
    browser
      .drupalRelativeURL('/test-page')
      .waitForElementVisible('body', 1000)
      .assert.containsText('body', 'Test page text')
      .drupalRelativeURL('/node/add/page')
      .setValue('input[name=title]', 'A new node')
      .setValue('input[name="body[0][value]"]', 'The main body')
      .click('#edit-submit')
      .end();
  },

};
