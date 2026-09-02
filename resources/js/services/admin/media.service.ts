import type { MediaAsset, MediaPurpose } from '@/types/admin';
import { uploadAdminMedia } from '@/lib/admin-api';

export const mediaService = {
    upload(file: File, purpose: MediaPurpose): Promise<MediaAsset> {
        return uploadAdminMedia(file, purpose);
    },
};
