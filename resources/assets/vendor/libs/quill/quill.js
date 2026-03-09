import Quill from 'quill';

import ImageResize from 'quill-image-resize-module--fix-imports-error';
Quill.register('modules/imageResize', ImageResize);

try {
  window.Quill = Quill;
} catch (e) {}

export { Quill };
