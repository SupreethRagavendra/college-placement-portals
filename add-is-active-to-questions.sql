-- Add missing columns to questions table in Supabase
-- Run this in your Supabase SQL editor

-- First, rename question_text to question if needed
DO $$ 
BEGIN
    IF EXISTS (
        SELECT 1 FROM information_schema.columns 
        WHERE table_name = 'questions' 
        AND column_name = 'question_text'
        AND table_schema = 'public'
    ) AND NOT EXISTS (
        SELECT 1 FROM information_schema.columns 
        WHERE table_name = 'questions' 
        AND column_name = 'question'
        AND table_schema = 'public'
    ) THEN
        ALTER TABLE public.questions 
        RENAME COLUMN question_text TO question;
        
        RAISE NOTICE 'Renamed question_text to question';
    END IF;
END $$;

-- Add the is_active column if it doesn't exist
DO $$ 
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.columns 
        WHERE table_name = 'questions' 
        AND column_name = 'is_active'
        AND table_schema = 'public'
    ) THEN
        ALTER TABLE public.questions 
        ADD COLUMN is_active BOOLEAN NOT NULL DEFAULT true;
        
        -- Create index for better performance
        CREATE INDEX IF NOT EXISTS idx_questions_is_active ON public.questions(is_active);
        
        RAISE NOTICE 'Added is_active column to questions table';
    ELSE
        RAISE NOTICE 'is_active column already exists in questions table';
    END IF;
END $$;

-- Handle correct_option column type mismatch
DO $$ 
BEGIN
    -- Check if correct_option is text and needs to be converted to integer
    IF EXISTS (
        SELECT 1 FROM information_schema.columns 
        WHERE table_name = 'questions' 
        AND column_name = 'correct_option'
        AND data_type = 'text'
        AND table_schema = 'public'
    ) THEN
        -- Add a temporary column
        ALTER TABLE public.questions 
        ADD COLUMN correct_option_int INTEGER;
        
        -- Convert text values to integers (assuming they are '0', '1', '2', '3')
        UPDATE public.questions 
        SET correct_option_int = CASE 
            WHEN correct_option = '0' THEN 0
            WHEN correct_option = '1' THEN 1
            WHEN correct_option = '2' THEN 2
            WHEN correct_option = '3' THEN 3
            ELSE 0
        END;
        
        -- Drop the old column and rename the new one
        ALTER TABLE public.questions 
        DROP COLUMN correct_option;
        
        ALTER TABLE public.questions 
        RENAME COLUMN correct_option_int TO correct_option;
        
        RAISE NOTICE 'Converted correct_option from text to integer';
    END IF;
END $$;

-- Also add other missing columns that might be needed by Laravel models
DO $$ 
BEGIN
    -- Add difficulty column if it doesn't exist
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.columns 
        WHERE table_name = 'questions' 
        AND column_name = 'difficulty'
        AND table_schema = 'public'
    ) THEN
        ALTER TABLE public.questions 
        ADD COLUMN difficulty TEXT DEFAULT 'Medium' CHECK (difficulty IN ('Easy', 'Medium', 'Hard'));
        
        RAISE NOTICE 'Added difficulty column to questions table';
    END IF;
    
    -- Add time_per_question column if it doesn't exist
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.columns 
        WHERE table_name = 'questions' 
        AND column_name = 'time_per_question'
        AND table_schema = 'public'
    ) THEN
        ALTER TABLE public.questions 
        ADD COLUMN time_per_question INTEGER DEFAULT 60;
        
        RAISE NOTICE 'Added time_per_question column to questions table';
    END IF;
    
    -- Add timestamps if they don't exist
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.columns 
        WHERE table_name = 'questions' 
        AND column_name = 'created_at'
        AND table_schema = 'public'
    ) THEN
        ALTER TABLE public.questions 
        ADD COLUMN created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
        ADD COLUMN updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW();
        
        RAISE NOTICE 'Added timestamps to questions table';
    END IF;
END $$;
