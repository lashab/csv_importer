export default {
  '@tags': ['csv_importer'],
  before: function(browser) {
    browser
      .drupalInstall();
  },
  after: function(browser) {
    browser
      .drupalUninstall();
  }
};
