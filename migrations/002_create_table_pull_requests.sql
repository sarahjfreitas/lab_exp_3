CREATE TABLE [dbo].[pull_requests]
(
	[id_pull_request] UNIQUEIDENTIFIER NOT NULL DEFAULT NEWID(),
	[id_repositorio] UNIQUEIDENTIFIER NOT NULL,
	[created_at] DATETIME NOT NULL,
	[closed_at] DATETIME NOT NULL,
	[state] VARCHAR(100) NOT NULL,
	[diff_size] INT NOT NULL,
	[files] INT NOT NULL,
	[description] NVARCHAR(MAX) NOT NULL,
	[comments] INT NOT NULL,
	[reviews] INT NOT NULL,
	[assignees] INT NOT NULL
	CONSTRAINT [PK_dbo__pull_requests__id_pull_request] PRIMARY KEY CLUSTERED
	(
		[id_pull_request] ASC
	)
	WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
)
ON [PRIMARY]
GO